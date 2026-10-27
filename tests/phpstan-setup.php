<?php

/*
 * This file is part of datlechin/flarum-ai.
 *
 * Copyright (c) 2025 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Flarum\Testing\integration\Setup\SetupScript;
use Flarum\Testing\integration\UsesTmpDir;

require __DIR__.'/../vendor/autoload.php';

// flarum/phpstan installs the test site from every parallel worker, and they race. Install once here instead.
$tmp = (new class() {
    use UsesTmpDir;
})->tmpDir();

if (! file_exists("$tmp/config.php")) {
    (new SetupScript())->run();
}
