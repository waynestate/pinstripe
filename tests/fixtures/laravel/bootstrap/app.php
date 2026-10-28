<?php

use Illuminate\Foundation\Application;

// A minimal Laravel application, just enough for Larastan to boot and resolve
// the framework's helpers and Eloquent. This fixture exists to prove that
// Pinstripe's laravel.neon preset loads Larastan through the extension
// installer and that Larastan's own checks then run.
return Application::configure(basePath: dirname(__DIR__))
    ->create();
