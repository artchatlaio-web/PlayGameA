@extends('layouts.app')

@section('content')
<div class="hero">
    <h1 class="hero-title">{{ __('Download Catalog') }}</h1>
    <p class="hero-subtitle">{{ __('Downloadable Games') }} — 20 premium titles ready for fast download.</p>
</div>

<div class="games-grid">
    @foreach($games as $game)
        @php
            $imgSrc = str_starts_with($game->image, 'IMAGE_') ? '/covers/' . $game->slug . '.svg' : $game->image;
            $dlLink = str_starts_with($game->download_link, 'DOWNLOAD_LINK_') ? route('download.file', $game->id) : $game->download_link;
        @endphp
        <div class="glass-card">
            <img src="{{ $imgSrc }}" alt="{{ $game->title }}" class="card-cover">
            <span class="card-badge">{{ $game->category }}</span>
            <h3 class="card-title">{{ $game->title }}</h3>
            <p class="card-desc">{{ $game->description }}</p>
            <div style="font-size:0.75rem; color:#64748b; margin-bottom:0.8rem; word-break:break-all;">
                Source: {{ $game->download_link }}
            </div>
            <div class="card-actions">
                <a href="{{ $dlLink }}" class="btn btn-primary btn-block">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    {{ __('Download') }}
                </a>
            </div>
        </div>
    @endforeach
</div>
@endsection

