<?php

/*
 * Consumer-style Rector config: it takes the Pinstripe preset from the
 * installed package and adds only this app's paths, exactly as the docs
 * show an app doing it. PresetsTest runs this fixture's own rector binary
 * against it to prove the preset applies the composer-based Laravel sets.
 */

$base = require __DIR__ . '/vendor/waynestate/pinstripe/rector/base.php';

return $base->withPaths([__DIR__ . '/src']);
