<?php


namespace tweet9ra\Logux\Laravel;

use tweet9ra\Logux\ActionsDispatcherBase;
use tweet9ra\Logux\App;
use tweet9ra\Logux\StackActionsDispatcher;

/**
 * Helper for testing
 */
trait MakingLoguxRequests
{
    /**
     * @param string $actionType
     * @param string|int|false $userId
     * @param array $arguments
     * @param bool $checkResponseStatus
     * @return LoguxTestResponse
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
     * @return LoguxTestResponse
     */
    public function loguxRequest(array $commands, $checkResponseStatus = true)
    {
        $response = $this->postJson(config('logux.endpoint_url'), [
            'version' => config('LOGUX_PROTOCOL_VERSION', 2),
            'password' => config('LOGUX_PASSWORD', 'secret'),
            'commands' => $commands
        ]);

        $loguxResponse = new LoguxTestResponse($response);

        if ($checkResponseStatus) {
            $loguxResponse->assertStatus(200);
        }

        return $loguxResponse;
    }

    public function assertLoguxActionDispatched(string $actionType)
    {
        $action = app(ActionsDispatcherBase::class)->search($actionType);
        $this->assertTrue(!empty($action), "Action $actionType was not dispatched");
        return $action;
    }
}