<?php

use Swoole\Process\Manager;
use Swoole\Process\Pool;

$pm = new Manager();

for ($i = 1; $i < 3; $i++) {
    echo __LINE__ . ' ' . date('c') . PHP_EOL;
    $pm->add(function (Pool $pool, int $workerId) use ($i) {
        echo (date('c') . " [Worker #{$workerId}] WorkerStart, pid: " . posix_getpid() . "\n");
        sleep($i);
    });
    echo __LINE__ . ' ' . date('c') . PHP_EOL;
}

    echo __LINE__ . ' ' . date('c') . PHP_EOL;
$pm->start();
