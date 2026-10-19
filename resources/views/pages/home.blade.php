@extends('layouts.app')

@section('title', 'Home - ' . config('app.name', 'KUPKOP'))

@section('content')
<div class="hero">
    <h1>Welcome to {{ config('app.name', 'KUPKOP') }}</h1>
    <p class="subtitle">Standard Laravel project structure is set up and ready for development.</p>
</div>

<div class="grid grid-2">
    <x-card title="Project Architecture">
        <p>This application follows standard Laravel conventions:</p>
        <ul class="feature-list">
            <li><strong>MVC Pattern:</strong> Clear separation between Models, Views, and Controllers.</li>
            <li><strong>Database:</strong> SQLite with Laravel migrations (<code>database/database.sqlite</code>).</li>
            <li><strong>Blade Templating:</strong> Clean master layout with reusable components.</li>
            <li><strong>Resource Routing:</strong> Clean, structured routes in <code>routes/web.php</code>.</li>
        </ul>
    </x-card>

    <x-card title="System Status">
        <div class="status-box">
            <p><strong>Environment:</strong> {{ app()->environment() }}</p>
            <p><strong>Laravel Version:</strong> {{ Illuminate\Foundation\Application::VERSION }}</p>
            <p><strong>PHP Version:</strong> {{ PHP_VERSION }}</p>
            <p><strong>Database Driver:</strong> {{ config('database.default') }}</p>
        </div>
    </x-card>
</div>
@endsection
