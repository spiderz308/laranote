<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] antialiased flex flex-col min-h-screen">
        <header class="w-full max-w-4xl mx-auto px-6 py-6 flex items-center justify-between">
            <span class="text-lg font-semibold tracking-tight">{{ config('app.name', 'Laravel') }}</span>
            @if (Route::has('login'))
                <nav class="flex items-center gap-3">
                    @auth
                        <a
                            href="{{ url('/dashboard') }}"
                            class="inline-flex items-center px-4 py-2 bg-[#1b1b18] dark:bg-[#EDEDEC] text-white dark:text-[#1C1C1A] rounded-md text-sm font-medium hover:opacity-80 transition"
                        >
                            Dashboard
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-black/5 dark:hover:bg-white/10 transition"
                        >
                            Log in
                        </a>
                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="inline-flex items-center px-4 py-2 bg-[#1b1b18] dark:bg-[#EDEDEC] text-white dark:text-[#1C1C1A] rounded-md text-sm font-medium hover:opacity-80 transition"
                            >
                                Register
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>

        <main class="flex-1 flex items-center justify-center px-6">
            <div class="text-center max-w-md">
                <h1 class="text-5xl font-bold tracking-tight mb-4">
                    {{ config('app.name', 'Laravel') }}
                </h1>
                <p class="text-lg text-[#706f6c] dark:text-[#A1A09A]">
                    Your notes, folders, and collaboration — all in one place.
                </p>

                @guest
                    <div class="mt-8 flex items-center justify-center gap-3">
                        @if (Route::has('login'))
                            <a
                                href="{{ route('login') }}"
                                class="inline-flex items-center px-6 py-3 bg-[#1b1b18] dark:bg-[#EDEDEC] text-white dark:text-[#1C1C1A] rounded-md text-sm font-semibold hover:opacity-80 transition"
                            >
                                Get started
                            </a>
                        @endif
                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="inline-flex items-center px-6 py-3 border border-black/20 dark:border-white/20 rounded-md text-sm font-semibold hover:bg-black/5 dark:hover:bg-white/10 transition"
                            >
                                Create an account
                            </a>
                        @endif
                    </div>
                @else
                    <div class="mt-8">
                        <a
                            href="{{ url('/dashboard') }}"
                            class="inline-flex items-center px-6 py-3 bg-[#1b1b18] dark:bg-[#EDEDEC] text-white dark:text-[#1C1C1A] rounded-md text-sm font-semibold hover:opacity-80 transition"
                        >
                            Go to dashboard
                        </a>
                    </div>
                @endguest
            </div>
        </main>
    </body>
</html>