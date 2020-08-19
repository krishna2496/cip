<?php

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$app->withFacades();
$app->withEloquent();

$app->configure('app');
$app->configure('database');
$app->configure('errors');
$app->configure('messages');
$app->configure('constants'); //constant file config
$app->configure('filesystems');
$app->configure('queue');
$app->configure('mail');
$app->configure('services');

$app->register(Flipbox\LumenGenerator\LumenGeneratorServiceProvider::class);


/**
 * mailer package registration
 */
$app->register(Illuminate\Notifications\NotificationServiceProvider::class);
$app->register(\Illuminate\Mail\MailServiceProvider::class);
$app->register(\LaravelMandrill\MandrillServiceProvider::class);
$app->alias('mailer', \Illuminate\Contracts\Mail\Mailer::class);
$app->alias('mailer', \Illuminateminate\Mail\Mailer::class);
$app->alias('mailer', \Illuminate\Contracts\Mail\MailQueue::class);
$app->alias('mail.manager', Illuminate\Mail\MailManager::class);
$app->alias('mail.manager', Illuminate\Contracts\Mail\Factory::class);


/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

// $app->middleware([
//     App\Http\Middleware\LoadSite::class
// ]);

$app->routeMiddleware([
    'localization' => App\Http\Middleware\LocalizationMiddleware::class,
    'JsonApiMiddleware' => App\Http\Middleware\JsonApiMiddleware::class,
    'PaginationMiddleware' => App\Http\Middleware\PaginationMiddleware::class,
    'RedirectInvalidIps' => App\Http\Middleware\RedirectInvalidIPs::class
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(App\Providers\AppServiceProvider::class);
// $app->register(App\Providers\AuthServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);
// $app->register(Urameshibr\Providers\FormRequestServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/web.php';
});

return $app;
