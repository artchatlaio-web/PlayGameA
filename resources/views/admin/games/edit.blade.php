@extends('layouts.app')

@section('content')
<div class="glass-card" style="max-width:650px; margin:2rem auto; padding:2rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
        <h2 style="font-size:1.4rem; font-weight:700;">Edit Game: {{ $game->title }}</h2>
        <a href="{{ route('admin.games') }}" class="btn btn-outline btn-sm">&larr; Back</a>
    </div>

    <form action="{{ route('admin.games', ['action' => 'update', 'id' => $game->id]) }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label">Game Title</label>
            <input type="text" name="title" class="form-control" value="{{ old('title', $game->title) }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Category</label>
            <input type="text" name="category" class="form-control" value="{{ old('category', $game->category) }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" rows="3" class="form-control" required>{{ old('description', $game->description) }}</textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Cover Image URL or Placeholder</label>
            <input type="text" name="image" class="form-control" value="{{ old('image', $game->image) }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Download Link (Optional)</label>
            <input type="text" name="download_link" class="form-control" value="{{ old('download_link', $game->download_link) }}">
        </div>
        <div class="form-group" style="display:flex; align-items:center; gap:0.5rem;">
            <input type="checkbox" name="is_downloadable" id="is_downloadable" value="1" {{ old('is_downloadable', $game->is_downloadable) ? 'checked' : '' }}>
            <label for="is_downloadable" style="font-size:0.88rem; color:#f8fafc; cursor:pointer;">
                Is Downloadable (In Download Catalog)
            </label>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Update Game</button>
    </form>
</div>
@endsection

