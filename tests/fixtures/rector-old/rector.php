<?php

declare(strict_types=1);

/*
 * The fixture's own rector.php: it uses the Pinstripe preset exactly the way
 * an app would, and adds only its own paths.
 */
$base = require __DIR__ . '/../../../rector/base.php';

return $base->withPaths([__DIR__ . '/src']);
