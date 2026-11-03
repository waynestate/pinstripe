<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreGreetingRequest;
use App\Models\Greeting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class GreetingController
{
    public function store(StoreGreetingRequest $request): RedirectResponse
    {
        Greeting::create($request->validated());

        return Redirect::to('/greetings');
    }
}
