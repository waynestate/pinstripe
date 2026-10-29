<?php

declare(strict_types=1);

return [
    'parameters' => [
        'ignoreErrors' => [
            [
                'message' => '#^PHP-format error one\.$#',
                'count' => 4,
                'path' => 'src/One.php',
            ],
            [
                'message' => '#^PHP-format error two\.$#',
            ],
            'PHP-format plain error three',
        ],
    ],
];
