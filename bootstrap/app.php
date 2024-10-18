<?php

use GuzzleHttp\Exception\ServerException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function (Response $response) {
            $status = $response->getStatusCode();
            switch($status){
                case 500:
                    return response()->json([
                        'message' => 'An error has occured. Please try again later',
                    ], 400);
                case 404:
                    return response()->json([
                        'message' => 'Not found',
                    ], 404);
                case 401:
                    return response()->json([
                        'message' => 'Not authenticated',
                    ], 401);
                default:
                    return $response;
            }
        });
        

    })->create();

    
