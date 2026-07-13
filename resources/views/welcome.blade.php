<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>CLIuno TallStack template</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-sans">
        <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
            <div class="relative min-h-screen flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">
                <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                    <header class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3">
                        <div class="flex lg:justify-center lg:col-start-2">
                            <h1 class="text-2xl font-semibold text-black dark:text-white">CLIuno</h1>
                        </div>
                        @if (Route::has('login'))
                            <livewire:welcome.navigation />
                        @endif
                    </header>

                    <main class="mt-6">
                        <div class="flex flex-col items-center gap-6 rounded-lg bg-white p-10 text-center shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:bg-zinc-900 dark:ring-zinc-800">
                            <h2 class="text-3xl font-semibold text-black dark:text-white">CLIuno TallStack template</h2>

                            <p class="max-w-xl text-sm/relaxed">
                                A full-stack demo app &mdash; auth, todos, posts, comments and follows &mdash; built with Tailwind, Alpine, Laravel and Livewire on the shared CLIuno REST contract.
                            </p>

                            <div class="flex items-center gap-4">
                                <a
                                    href="{{ route('login') }}"
                                    class="rounded-md bg-[#FF2D20] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#FF2D20]/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF2D20] focus-visible:ring-offset-2"
                                >
                                    Log in
                                </a>

                                @if (Route::has('register'))
                                    <a
                                        href="{{ route('register') }}"
                                        class="rounded-md px-5 py-2.5 text-sm font-semibold text-black ring-1 ring-black/10 transition hover:bg-black/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF2D20] dark:text-white dark:ring-white/20 dark:hover:bg-white/5"
                                    >
                                        Register
                                    </a>
                                @endif
                            </div>
                        </div>
                    </main>

                    <footer class="py-16 text-center text-sm text-black dark:text-white/70">
                        Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
                    </footer>
                </div>
            </div>
        </div>
    </body>
</html>
