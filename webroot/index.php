<?php
/**
 *
 */

require dirname(__DIR__) . '/vendor/autoload.php';

use Dietcube\Dispatcher;

Dispatcher::invoke(
    '\\SampleApp\\Application',
    dirname(__DIR__) . '/app',
    Dispatcher::getEnv()
);
