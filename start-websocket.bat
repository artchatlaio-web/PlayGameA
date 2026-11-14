@echo off
title PlayGame WebSocket Server
cls
echo Starting PlayGame Multiplayer WebSocket Server on port 8080...
echo.
php artisan websocket:serve --port=8080
pause

