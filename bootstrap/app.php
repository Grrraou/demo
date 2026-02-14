<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        ['prefix' => 'api', 'middleware' => ['auth:sanctum']],
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureTeamMemberIsAdmin::class,
            'edit.customers' => \App\Http\Middleware\EnsureTeamMemberCanEditCustomers::class,
            'manage.articles' => \App\Http\Middleware\EnsureTeamMemberCanManageArticles::class,
            'view.inventory' => \App\Http\Middleware\EnsureTeamMemberCanViewInventory::class,
            'edit.inventory' => \App\Http\Middleware\EnsureTeamMemberCanEditInventory::class,
            'current.company' => \App\Http\Middleware\EnsureCurrentOwnedCompany::class,
        ]);
        $middleware->web(append: [\App\Http\Middleware\EnsureCurrentOwnedCompany::class]);
        $middleware->redirectGuestsTo('/');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            if ($e instanceof AuthenticationException) {
                return redirect()->guest('/');
            }

            $statusCode = 500;
            if ($e instanceof HttpException) {
                $statusCode = $e->getStatusCode();
            }

            $view = "errors.{$statusCode}";
            if (! view()->exists($view)) {
                $view = 'errors.500';
            }

            return response()->view($view, ['exception' => $e], $statusCode);
        });
    })->create();
