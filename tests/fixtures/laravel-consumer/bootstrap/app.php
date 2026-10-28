<?php

use Illuminate\Foundation\Application;

// A minimal Laravel application, just enough for Larastan to boot and define
// LARAVEL_VERSION. This fixture exists to prove that Pinstripe's laravel.neon
// preset loads Larastan through the extension installer.
return Application::configure(basePath: dirname(__DIR__))
    ->create();
