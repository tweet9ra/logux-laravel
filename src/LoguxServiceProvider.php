<?php

namespace tweet9ra\Logux\Laravel;

use App\Exceptions\Handler;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use tweet9ra\Logux\App as LoguxApp;
use tweet9ra\Logux\DispatchableAction;
use tweet9ra\Logux\EventsHandler;
use tweet9ra\Logux\ProcessableAction;

class LoguxServiceProvider extends ServiceProvider
{
    protected static $routes;

    public function register()
    {
        if (($password = config('logux.password')) && ($url = config('logux.control_url'))) {
            LoguxApp::getInstance()->loadConfig(
                $password,
                $url,
                config('logux.protocol_version')
            );
        }

        $this->app->singleton(LoguxApp::class, function($app) {
            return LoguxApp::getInstance();
        });
    }

    public function boot()
    {
        $app = LoguxApp::getInstance();

        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('logux.php')
        ], 'config');

        $this->publishes([
            __DIR__.'/../config/routes.php' => base_path('/routes/logux.php')
        ], 'routes');

        $this->publishes([
            __DIR__.'/../config/subscription-routes.php' => base_path('/routes/logux-subscription.php')
        ], 'routes');

        // Authenticate users before each action
        $app->getEventsHandler()
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
        $route = Route::post(config('logux.endpoint_url'), function () use ($app) {
            $app->setActionsMap($this->loadRoutes());

            $request = json_decode(request()->getContent(), true);
            $responseContent = $app->processRequest($request);

            return json_encode($responseContent, JSON_UNESCAPED_UNICODE);
        });

        if ($middleware = config('logux.middleware')) {
            if (is_string($middleware)) {
                $middleware = explode(',', $middleware);
            }
            $route->middleware($middleware);
        }

        $this->app->bind(DispatchableAction::class, function () {
            return new DispatchableAction();
        });
    }

    private function loadRoutes()
    {
        if (!self::$routes) {
            self::$routes = require config('logux.routes_path', base_path('/routes/logux.php'));
        }

        return self::$routes;
    }
}
