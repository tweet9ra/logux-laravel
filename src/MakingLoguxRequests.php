<?php


namespace tweet9ra\Logux\Laravel;

use tweet9ra\Logux\App;
use tweet9ra\Logux\StackActionsDispatcher;

/**
 * Helper for testing
 */
trait MakingLoguxRequests
{
    /**
     * Keeps in itself dispatched actions
     * @var StackActionsDispatcher $loguxDispatcher
     */
    protected $loguxDispatcher;

    protected function setUp(): void
    {
        $this->setLoguxDispatcher();
        parent::setUp();
    }

    protected function setLoguxDispatcher()
    {
        $dispatcher = new StackActionsDispatcher();
        App::getInstance()->setActionsDispatcher($dispatcher);
        $this->loguxDispatcher = $dispatcher;
    }

    /**
     * @param string $actionType
     * @param string|int|false $userId
     * @param array $arguments
     * @param bool $checkResponseStatus
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    protected function loguxCallAction(
        string $actionType,
        $userId = null,
        array $arguments = [],
        $checkResponseStatus = true
    )
    {
        $t = time();

        if (!$userId) {
            $userId = 'false';
        }

        return $this->loguxRequest([[
            'action',
            array_merge(['type' => $actionType], $arguments),
            ['id' => "$t $userId:testtt:testtt $userId", 'time' => $t]
        ]], $checkResponseStatus);
    }

    /**
     * @param array $commands
     * @param bool $checkResponseStatus
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function loguxRequest(array $commands, $checkResponseStatus = true)
    {
        $response = $this->postJson(config('logux.endpoint_url'), [
            'version' => App::getInstance()->getVersion(),
            'password' => App::getInstance()->getControlPassword(),
            'commands' => $commands
        ]);

        if ($checkResponseStatus) {
            $response->assertStatus(200);
        }

        return $response;
    }
}