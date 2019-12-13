<?php

use \tweet9ra\Logux\ProcessableAction;

return [
    'chat/:chatId/:channelId' => 'Foo@subscribe',
    'chat/:chatId' => function (ProcessableAction $action, string $chatId, int $channelId) {
        // Your logic
    }
];