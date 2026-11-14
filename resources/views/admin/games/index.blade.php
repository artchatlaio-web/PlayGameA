@extends('layouts.app')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
    <div>
        <h1 style="font-size:1.6rem; font-weight:800;">Admin: Games Management</h1>
        <p style="font-size:0.88rem; color:#94a3b8;">Manage catalog and web playable games (Full CRUD)</p>
    </div>
    <a href="{{ route('admin.games', ['action' => 'create']) }}" class="btn btn-primary btn-sm">+ Add New Game</a>
</div>

<div class="glass-card" style="padding:0; overflow:hidden;">
    <table class="table-glass">
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Title</th>
                <th>Category</th>
                <th>Type</th>
                <th>Download Link</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($games as $g)
                <tr>
                    <td>{{ $g->id }}</td>
                    <td>
                        @php
                            $img = str_starts_with($g->image, 'IMAGE_') ? '/covers/' . $g->slug . '.svg' : $g->image;
                        @endphp
                        <img src="{{ $img }}" style="width:40px; height:28px; object-fit:cover; border-radius:4px;">
                    </td>
                    <td style="font-weight:600;">{{ $g->title }}</td>
                    <td><span class="card-badge" style="margin:0;">{{ $g->category }}</span></td>
                    <td>
                        <span style="font-size:0.75rem; padding:2px 6px; border-radius:4px; background:{{ $g->is_downloadable ? 'rgba(59,130,246,0.2)' : 'rgba(16,185,129,0.2)' }}; color:{{ $g->is_downloadable ? '#93c5fd' : '#6ee7b7' }};">
                            {{ $g->is_downloadable ? 'Downloadable' : 'Playable' }}
                        </span>
                    </td>
                    <td style="font-size:0.8rem; color:#64748b; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        {{ $g->download_link ?? '—' }}
                    </td>
                    <td style="display:flex; gap:0.4rem;">
                        <a href="{{ route('admin.games', ['action' => 'edit', 'id' => $g->id]) }}" class="btn btn-outline btn-sm">Edit</a>
                        <form action="{{ route('admin.games', ['action' => 'delete', 'id' => $g->id]) }}" method="POST" onsubmit="return confirm('Delete this game?');">
                            @csrf
                            <button type="submit" class="btn btn-sm" style="background:#ef4444; color:#fff;">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div style="margin-top:1.2rem;">
    {{ $games->links() }}
</div>
@endsection

