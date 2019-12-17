<?php

namespace tweet9ra\Logux\Laravel;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use tweet9ra\Logux\App as LoguxApp;
use tweet9ra\Logux\DispatchableAction;
use tweet9ra\Logux\ProcessableAction;

class LoguxServiceProvider extends ServiceProvider
{
    public function register()
    {
        LoguxApp::getInstance()->loadConfig(
            config('logux.password'),
            config('logux.control_url'),
            config('logux.protocol_version')
        );

        $this->app->singleton(LoguxApp::class, function($app) {
            return LoguxApp::getInstance();
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

        LoguxApp::getInstance()
            ->addEvent(
                LoguxApp::BEFORE_PROCESS_ACTION,
                function (ProcessableAction $action) {
                    if ($action->userId) {
                        Auth::loginUsingId($action->userId);
                    } else {
                        Auth::logout();
                    }
                }
            );

        $route = Route::post(config('logux.endpoint_url'), function () {
            /** @var LoguxApp $app */
            $app = app(LoguxApp::class);

            $app->setActionsMap($this->loadRoutes());

            $request = json_decode(request()->getContent(), true);
            $responseContent = $app->processRequest($request);

            return json_encode($responseContent);
        });

        if ($middleware = config('logux.middleware')) {
            $route->middleware($middleware);
        }

        $this->app->bind(DispatchableAction::class, function () {
            return new DispatchableAction();
        });
    }

    private function loadRoutes()
    {
        return require_once config('logux.routes_path', base_path('/routes/logux.php'));
    }
}
