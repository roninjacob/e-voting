<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <main class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 p-md-5">
                            <p class="text-uppercase text-primary fw-semibold small mb-2">Development baseline</p>
                            <h1 class="h2 mb-3">{{ config('app.name') }}</h1>
                            <p class="mb-0 text-body-secondary">
                                The system foundation is ready for module development. Voting features are not yet implemented.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>
