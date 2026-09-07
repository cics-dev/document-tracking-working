<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
<<<<<<< HEAD
        $middleware->appendToGroup('web', \App\Http\Middleware\NoCache::class);
=======
        //
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
