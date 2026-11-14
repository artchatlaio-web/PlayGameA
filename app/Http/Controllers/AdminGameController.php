<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminGameController extends Controller
{
    private function checkAdmin()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
    }

    public function handle(Request $request, ?string $action = null, ?string $id = null)
    {
        $this->checkAdmin();

        if ($request->isMethod('get') && !$action) {
            return $this->index();
        }
        if ($request->isMethod('get') && $action === 'create') {
            return $this->create();
        }
        if ($request->isMethod('post') && !$action) {
            return $this->store($request);
        }
        if ($request->isMethod('get') && $action === 'edit' && $id) {
            return $this->edit($id);
        }
        if (($request->isMethod('put') || $request->isMethod('post')) && $action === 'update' && $id) {
            return $this->update($request, $id);
        }
        if (($request->isMethod('delete') || $request->isMethod('post')) && $action === 'delete' && $id) {
            return $this->destroy($id);
        }
        abort(404);
    }

    public function index()
    {
        $games = Game::orderByDesc('id')->paginate(15);
        return view('admin.games.index', compact('games'));
    }

    public function create()
    {
        return view('admin.games.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:100',
            'category' => 'required|string|max:50',
            'description' => 'required|string',
            'image' => 'required|string',
            'download_link' => 'nullable|string',
            'is_downloadable' => 'nullable|boolean',
        ]);

        $data['slug'] = Str::slug($data['title']) . '-' . rand(100, 999);
        $data['is_downloadable'] = $request->boolean('is_downloadable');

        Game::create($data);

        return redirect()->route('admin.games')->with('success', 'Game created successfully.');
    }

    public function edit($id)
    {
        $game = Game::findOrFail($id);
        return view('admin.games.edit', compact('game'));
    }

    public function update(Request $request, $id)
    {
        $game = Game::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:100',
            'category' => 'required|string|max:50',
            'description' => 'required|string',
            'image' => 'required|string',
            'download_link' => 'nullable|string',
            'is_downloadable' => 'nullable|boolean',
        ]);

        $data['is_downloadable'] = $request->boolean('is_downloadable');
        $game->update($data);

        return redirect()->route('admin.games')->with('success', 'Game updated successfully.');
    }

    public function destroy($id)
    {
        $game = Game::findOrFail($id);
        $game->delete();

        return redirect()->route('admin.games')->with('success', 'Game deleted.');
    }
}

