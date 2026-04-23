@extends('layouts.app')

@section('content')
<div x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false" class="flex min-h-screen flex-col">
    @include('layouts.userpanel.sidebar')
    @include('layouts.userpanel.header')

    @hasSection('userpanel_content')
        @yield('userpanel_content')
    @else
        {{ $slot ?? '' }}
    @endif
</div>
@endsection
