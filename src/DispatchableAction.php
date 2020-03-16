<?php

namespace tweet9ra\Logux\Laravel;

use tweet9ra\Logux\App;

class DispatchableAction extends \tweet9ra\Logux\DispatchableAction
{
    public function dispatch()
    {
        /** @var App $loguxApp */
        $loguxApp = app(App::class);
        $loguxApp->dispatchAction($this);
    }
}