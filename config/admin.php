<?php

return [

    'auto_paused_project' => [
        'to_be_notified' => explode(',', env('ADMIN_TO_BE_NOTIFIED', [])),
    ],

];
