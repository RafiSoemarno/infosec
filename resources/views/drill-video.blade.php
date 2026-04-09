@extends('layouts.app')

@section('title', 'Drill - Information Security Video')

@section('content')
    <section class="content-span-12 fade-in-up">
        <div class="panel-card p-4">
            <h2 class="mb-3">Information Security Drill</h2>
            <p class="mb-4">Watch the drill video below. You may return to the drill page when finished.</p>
            <div style="position: relative; width: 100%; background: #000; border-radius: 8px; overflow: hidden;">
                <video
                    controls
                    autoplay
                    style="width: 100%; max-height: 70vh; display: block;"
                >
                    <source src="{{ url('/drill/video/stream') }}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
            <div class="mt-4">
                <a href="{{ url('/drill') }}" class="drill-action-btn drill-action-btn--watch">
                    &larr; Back to Drill
                </a>
            </div>
        </div>
    </section>
@endsection
