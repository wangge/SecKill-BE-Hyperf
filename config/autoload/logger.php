<?php

return [
    'default' => [
        ['handler' => [
            'class' => Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/hyperf.log',
                'level' => \Monolog\Logger::INFO,
            ],
        ],
        'formatter' => [
            'class' => \Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                'format' => null,
                'dateFormat' => null,
                'allowInlineLineBreaks' => true,
            ]
        ]],
        ['handler' => [
            'class' => \Monolog\Handler\StreamHandler::class,
            'constructor' => [
                'stream' => BASE_PATH . '/runtime/logs/hyperf-debug.log',
                'level' => \Monolog\Logger::DEBUG,
            ],
        ],
            'formatter' => [
                'class' => \Monolog\Formatter\LineFormatter::class,
                'constructor' => [
                    'format' => null,
                    'dateFormat' => null,
                    'allowInlineLineBreaks' => true,
                ]
            ]]
    ],
];
