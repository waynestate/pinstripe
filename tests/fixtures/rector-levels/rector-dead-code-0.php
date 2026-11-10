<?php

declare(strict_types=1);

/*
 * One of the fixture's rector configs: the levels preset used exactly the way
 * an app would, at one step of the climb, with only the fixture's own paths.
 */
$levels = require __DIR__ . '/../../../rector/levels.php';

return $levels(deadCode: 0)->withPaths([__DIR__ . '/src']);
