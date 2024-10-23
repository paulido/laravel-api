<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        
   
        $middleware->alias([
            // Sanctum
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,

            // Spatie
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'language' => \App\Http\Middleware\SetLocale::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function (Response $response) {
            // $status = $response->getStatusCode();
            // switch($status){
            //     case 500:
            //         // Log::error('Server error IDO', $response->getContent());
            //         return response()->json([
            //             'message' => 'An error has occured. Please try again later',
            //         ], 400);
            //     case 404 || 405 || 403:
            //         return response()->json([
            //             'message' => 'Not found',
            //         ], 404);
            //     case 401:
            //         return response()->json([
            //             'message' => 'Not authenticated',
            //         ], 401);
            //     default:
            //         return $response;
            // }
        });
        

    })->create();

    
