<?php

use Swoole\Process\Manager;
use Swoole\Process\Pool;

$pm = new Manager();

$pm->addBatch(2, function (Pool $pool, int $workerId) {
    $sleep = mt_rand(1, 3);
    echo (date('c') . " [Worker #{$workerId}] WorkerStart, pid: " . posix_getpid() . " delay:{$sleep}s\n");
    Co::sleep($sleep);
}, true);
echo __LINE__ . ' ' . date('c') . PHP_EOL;

$pm->start();
