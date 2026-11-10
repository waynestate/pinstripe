<?php

declare(strict_types=1);

/*
 * The fixture's own rector.php: it uses the strict-types preset exactly the
 * way an app would, and adds only its own paths.
 */
$strict = require __DIR__ . '/../../../rector/strict-types.php';

return $strict->withPaths([__DIR__ . '/src']);
