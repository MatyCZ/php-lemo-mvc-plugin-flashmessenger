<?php

namespace Lemo\Mvc;

return [
    'controller_plugins' => [
        'invokables' => [
            'lemoFlashMessenger' => Controller\Plugin\FlashMessenger::class,
        ],
    ],
];
