<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;

// A minimal Laravel 13 application: just enough for Larastan to boot and for
// Pinstripe's laravel.neon to analyse a real model, controller, route and
// form request.
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
    )
    ->withMiddleware()
    ->withExceptions()
    ->create();
