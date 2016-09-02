<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Digital\Console;

$console = new Application();
$console->add(new Console\Build());
$console->add(new Console\Download());

$console->run();
