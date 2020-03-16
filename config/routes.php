<?php

use \tweet9ra\Logux\ProcessableAction;
use \tweet9ra\Logux\DispatchableAction;

use Illuminate\Support\Facades\Auth;

return [
    'logux/subscribe' => require_once 'logux-subscription.php',
    /**
     * Check user credentials
     * @param string|null|false $userId
     * @param string|null $token
     * @param string $authId Authentication command ID
     * @return bool
     */
    'auth' => function (string $authId, string $userId = null, string $token = null): bool {
        if (!$userId) {
            return true;
        }
        
        return true;
    },
    'ANOTHER_ACTION' => 'ActionController@anotherAction',
    'ADD_CHAT_MESSAGE' => function (ProcessableAction $action) {
        (new DispatchableAction)
            ->setType('NEW_CHAT_MESSAGE')
            ->sendTo('channels', ['chats/1337'])
            ->sendTo('users', [Auth::id()]) // You can use auth facade here, Auth::id() == $action->uerId()
            ->setArguments(['message' => 'hello world'])
            ->setArgument('textColor', 'red')
            ->dispatch();

        $action->approved()->processed();
    }
];
