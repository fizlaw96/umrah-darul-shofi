<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use League\Flysystem\UnableToSetVisibility;
use League\Flysystem\UnableToWriteFile;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (PostTooLargeException $exception, Request $request) {
            $referer = $request->headers->get('referer');
            $applicationUrl = rtrim($request->getSchemeAndHttpHost(), '/').'/';
            $previousUrl = is_string($referer) && str_starts_with($referer, $applicationUrl)
                ? $referer
                : url('/');
            $separator = str_contains($previousUrl, '?') ? '&' : '?';

            return redirect()->to($previousUrl.$separator.'upload_error=1');
        });

        $exceptions->render(function (UnableToWriteFile|UnableToSetVisibility $exception, Request $request) {
            $message = 'Fail tidak dapat disimpan pada pelayan. Sila cuba semula atau hubungi pentadbir untuk semak storan pelayan.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->withErrors(['upload' => $message]);
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
