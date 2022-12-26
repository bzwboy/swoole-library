<?php

use Swoole\Process;
use Swoole\Coroutine;

$pool = new Process\Pool(2);
$pool->set(['enable_coroutine' => true]);
$pool->on('WorkerStart', function (Process\Pool $pool, $workerId) {
    /** 当前是 Worker 进程 */
    static $running = true;
    Process::signal(SIGTERM, function () use (&$running) {
        $running = false;
        echo "TERM\n";
    });
    echo ("[Worker #{$workerId}] WorkerStart, pid: " . posix_getpid() . "\n");
    while ($running) {
        Coroutine::sleep(5);
        echo "sleep 5\n";
    }
});
$pool->on('WorkerStop', function (\Swoole\Process\Pool $pool, $workerId) {
    echo ("[Worker #{$workerId}] WorkerStop\n");
});
$pool->start();
