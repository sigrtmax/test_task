<?php

use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidStatusTransitionException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // InsufficientStockException -> 422
        $exceptions->render(function (InsufficientStockException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        });

        // InvalidStatusTransitionException -> 422
        $exceptions->render(function (InvalidStatusTransitionException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        });

        // ModelNotFoundException -> 404
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                $previous = $e->getPrevious();
                $model = ($previous !== null && method_exists($previous, 'getModel'))
                    ? class_basename($previous->getModel())
                    : 'Resource';

                return response()->json(
                    ['message' => "{$model} not found."],
                    Response::HTTP_NOT_FOUND
                );
            }
        });
    })
    ->booted(function () {
        // Rate limiter for order creation endpoint (10/min per IP)
        RateLimiter::for('orders', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    })
    ->create();
