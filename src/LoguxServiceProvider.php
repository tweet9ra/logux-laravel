<?php

namespace tweet9ra\Logux\Laravel;

use App\Exceptions\Handler;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use tweet9ra\Logux\ActionsDispatcherBase;
use tweet9ra\Logux\App;
use tweet9ra\Logux\CommandsProcessor;
use tweet9ra\Logux\CurlActionsDispatcher;
use tweet9ra\Logux\DispatchableAction;
use tweet9ra\Logux\EventsHandler;
use tweet9ra\Logux\ProcessableAction;
use tweet9ra\Logux\StackActionsDispatcher;

class LoguxServiceProvider extends ServiceProvider
{
    protected static $routes;

    public function register()
    {
        $password = $this->config('password', 'secret');
        $url = $this->config('control_url');
        if (!$url && !$this->app->runningUnitTests()) {
            return;
        }

        $this->app->singleton('tweet9ra.logux.events_handler', function($app){
            return new EventsHandler();
        });

        $this->app->singleton('tweet9ra.logux.actions_dispatcher', function($app) use ($url) {
            return $this->app->runningUnitTests()
                ? new StackActionsDispatcher()
                : new CurlActionsDispatcher($url);
        });

        $this->app->singleton('tweet9ra.logux.commands_processor', function($app) {
            return new CommandsProcessor($app['tweet9ra.logux.events_handler']);
        });

        $this->app->singleton(App::class, function($app) use ($password) {
            return new App(
                $app['tweet9ra.logux.commands_processor'],
                $app['tweet9ra.logux.actions_dispatcher'],
                $app['tweet9ra.logux.events_handler'],
                $password,
                $this->config('protocol_version')
            );
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('logux.php')
        ], 'config');

        $this->publishes([
            __DIR__.'/../config/routes.php' => base_path('/routes/logux.php')
        ], 'routes');

        $this->publishes([
            __DIR__.'/../config/subscription-routes.php' => base_path('/routes/logux-subscription.php')
        ], 'routes');

        if (!$this->config()) {
            // Config is not published
            return;
        }

        $this->app->singleton(ActionsDispatcherBase::class, function ($app) {
            return $app['tweet9ra.logux.actions_dispatcher'];
        });


        /** @var App $loguxApp */
        $loguxApp = $this->app[App::class];

        // Authenticate users before each action
        $loguxApp->getEventsHandler()
            ->addEvent(
                EventsHandler::BEFORE_PROCESS_ACTION,
                function (ProcessableAction $action) {
                    if ($action->userId() && $action->userId() != 'false') {
                        Auth::loginUsingId($action->userId());
                    } else {
                        Auth::logout();
                    }
                }
            );

        // Registering route that handle logux requests
        $route = Route::post($this->config('endpoint_url'), function () use ($loguxApp) {
            $loguxApp->setActionsMap($this->loadRoutes());

            $request = json_decode(request()->getContent(), true);
            $responseContent = $loguxApp->processRequest($request);

            return json_encode($responseContent, JSON_UNESCAPED_UNICODE);
        });

        if ($middleware = $this->config('middleware')) {
            if (is_string($middleware)) {
                $middleware = explode(',', $middleware);
            }
            $route->middleware($middleware);
        }
    }

    protected function config($value = null, $default = null)
    {
        return config('logux' . ($value ? ".$value" : ''), $default);
    }

    private function loadRoutes()
    {
        if (!self::$routes) {
            self::$routes = require $this->config('routes_path', base_path('/routes/logux.php'));
        }

        return self::$routes;
    }
}
