@extends('layouts.app')

@php
    $memberSessionTimeoutMs = max(1, (int) config('auth.member_session_lifetime', 5)) * 60 * 1000;
@endphp

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

<form id="member-idle-logout-form" method="POST" action="{{ route('logout') }}" class="hidden" aria-hidden="true">
    @csrf
    <input type="hidden" name="inactive" value="1">
</form>

<script>
    (() => {
        const timeoutMs = {{ $memberSessionTimeoutMs }};
        const logoutForm = document.getElementById('member-idle-logout-form');

        if (! logoutForm || timeoutMs < 1000) {
            return;
        }

        let timerId = null;
        let loggingOut = false;

        const scheduleLogout = () => {
            window.clearTimeout(timerId);

            if (loggingOut) {
                return;
            }

            timerId = window.setTimeout(() => {
                loggingOut = true;
                logoutForm.requestSubmit();
            }, timeoutMs);
        };

        const isSameOrigin = (resource) => {
            if (! resource) {
                return false;
            }

            try {
                const target = resource instanceof Request
                    ? resource.url
                    : String(resource);

                return new URL(target, window.location.origin).origin === window.location.origin;
            } catch (error) {
                return false;
            }
        };

        const trackRequest = (resource) => {
            if (isSameOrigin(resource)) {
                scheduleLogout();
            }
        };

        if (typeof window.fetch === 'function') {
            const nativeFetch = window.fetch.bind(window);

            window.fetch = (...args) => {
                trackRequest(args[0]);

                return nativeFetch(...args);
            };
        }

        if (typeof window.XMLHttpRequest === 'function') {
            const nativeOpen = window.XMLHttpRequest.prototype.open;
            const nativeSend = window.XMLHttpRequest.prototype.send;

            window.XMLHttpRequest.prototype.open = function (method, url, ...args) {
                this.__memberIdleTimeoutUrl = url;

                return nativeOpen.call(this, method, url, ...args);
            };

            window.XMLHttpRequest.prototype.send = function (...args) {
                trackRequest(this.__memberIdleTimeoutUrl);

                return nativeSend.apply(this, args);
            };
        }

        document.addEventListener('submit', () => {
            scheduleLogout();
        }, true);

        scheduleLogout();
    })();
</script>
@endsection
