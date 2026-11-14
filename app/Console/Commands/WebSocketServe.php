<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class WebSocketServe extends Command
{
    protected $signature = 'websocket:serve {--port=8080}';
    protected $description = 'Start the local multiplayer WebSocket server';

    public function handle()
    {
        $port = (int) $this->option('port');
        $server = stream_socket_server("tcp://0.0.0.0:{$port}", $errno, $errstr);

        if (!$server) {
            $this->error("Server error: {$errstr} ({$errno})");
            return 1;
        }

        $this->info("WebSocket server running on 0.0.0.0:{$port}");

        $clients = [];
        $rooms = [];

        while (true) {
            $read = array_merge([$server], $clients);
            $write = null;
            $except = null;

            if (stream_select($read, $write, $except, 0, 200000) < 1) {
                continue;
            }

            if (in_array($server, $read)) {
                $client = stream_socket_accept($server);
                if ($client) {
                    $clients[] = $client;
                    $this->performHandshake($client);
                }
                unset($read[array_search($server, $read)]);
            }

            foreach ($read as $client) {
                $data = $this->decodeFrame($client);
                if ($data === false || $data === '') {
                    $idx = array_search($client, $clients);
                    unset($clients[$idx]);
                    fclose($client);
                    continue;
                }

                $msg = json_decode($data, true);
                if ($msg && isset($msg['room'])) {
                    $roomCode = $msg['room'];
                    $rooms[$roomCode][$msg['id'] ?? (int)$client] = [
                        'id' => $msg['id'] ?? (int)$client,
                        'name' => $msg['name'] ?? 'Player',
                        'team' => $msg['team'] ?? 'blue',
                        'x' => $msg['x'] ?? 100,
                        'y' => $msg['y'] ?? 100,
                        'score' => $msg['score'] ?? 0,
                    ];

                    $payload = $this->encodeFrame(json_encode([
                        'room' => $roomCode,
                        'players' => array_values($rooms[$roomCode]),
                    ]));

                    foreach ($clients as $sendClient) {
                        @fwrite($sendClient, $payload);
                    }
                }
            }
        }
    }

    private function performHandshake($client)
    {
        $headers = fread($client, 2048);
        if (preg_match('/Sec-WebSocket-Key: (.*)\r\n/', $headers, $matches)) {
            $key = trim($matches[1]);
            $accept = base64_encode(pack('H*', sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
            $upgrade = "HTTP/1.1 101 Switching Protocols\r\n" .
                "Upgrade: websocket\r\n" .
                "Connection: Upgrade\r\n" .
                "Sec-WebSocket-Accept: {$accept}\r\n\r\n";
            fwrite($client, $upgrade);
        }
    }

    private function decodeFrame($client)
    {
        $header = fread($client, 2);
        if (strlen($header) < 2) return false;

        $secondByte = ord($header[1]);
        $masked = ($secondByte >> 7) === 1;
        $len = $secondByte & 127;

        if ($len === 126) {
            $lenData = fread($client, 2);
            $len = unpack('n', $lenData)[1];
        } elseif ($len === 127) {
            $lenData = fread($client, 8);
            $len = unpack('J', $lenData)[1];
        }

        $masks = $masked ? fread($client, 4) : '';
        $data = '';
        while (strlen($data) < $len) {
            $buf = fread($client, min(1024, $len - strlen($data)));
            if ($buf === false || $buf === '') break;
            $data .= $buf;
        }

        if ($masked) {
            $unmasked = '';
            for ($i = 0; $i < strlen($data); $i++) {
                $unmasked .= $data[$i] ^ $masks[$i % 4];
            }
            return $unmasked;
        }

        return $data;
    }

    private function encodeFrame($payload)
    {
        $b1 = 0x81;
        $len = strlen($payload);

        if ($len < 126) {
            return pack('CC', $b1, $len) . $payload;
        } elseif ($len <= 0xFFFF) {
            return pack('CCn', $b1, 126, $len) . $payload;
        } else {
            return pack('CCNN', $b1, 127, 0, $len) . $payload;
        }
    }
}

