<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-orange-300 antialiased dark:bg-linear-to-b dark:from-neutral-850 dark:to-neutral-700">
        <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            <div class="bg-muted relative hidden h-full flex-col p-10 text-black lg:flex dark:border-e dark:border-neutral-800">
                <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('images/wisdom_inn1.jpeg') }}'); background-size: cover; background-position: center;">
                    <!-- Added a semi-transparent dark overlay to keep the text readable -->
                    <div class="absolute inset-0 bg-black/50"></div>
                </div>

                @php
                    [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
                @endphp

                <div class="relative z-20 mt-auto">
                    <blockquote class="space-y-2">
                        <flux:heading size="lg">&ldquo;{{ trim($message) }}&rdquo;</flux:heading>
                        <footer><flux:heading>{{ trim($author) }}</flux:heading></footer>
                    </blockquote>
                </div>
            </div>
            <div class="w-full lg:p-8 ">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    {{ $slot }}
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
