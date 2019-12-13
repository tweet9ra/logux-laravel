<?php

use \tweet9ra\Logux\ProcessableAction;
use \tweet9ra\Logux\DispatchableAction;

use Illuminate\Support\Facades\Auth;

return [
    /**
     * Processing subscribe action
     * @param \tweet9ra\Logux\ProcessableAction $action
     * @return void Return result of actions callback is not used, you must affect Action Object
     */
    'logux/subscribe' => require_once 'logux-subscription.php',
    /**
     * Check user credentials
     * @param string|null|false $userId
     * @param string|null $token
     * @param string $authId Authentication command ID
     * @return bool
     */
    'auth' => function (string $authId, $userId, $token) : bool
    {
        return true;
    },
    'ANOTHER_ACTION' => 'ActionController@anotherAction',
    'ADD_CHAT_MESSAGE' => function (ProcessableAction $action) {
        (new DispatchableAction)
            ->setType('NEW_CHAT_MESSAGE')
            ->sendTo('channels', ['chats/1337'])
            ->sendTo('users', [Auth::id()]) // You can use auth facade here, Auth::id() == $action->userId
            ->setArguments(['message' => 'hello world'])
            ->setArgument('textColor', 'red')
            ->dispatch();

        $action->approved()->processed();
    }
];
