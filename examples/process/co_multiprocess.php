<?php

use Swoole\Process;
use Swoole\Coroutine;
use Swoole\Coroutine\Server\Connection;

//多进程管理模块
$pool = new Process\Pool(4);
//让每个OnWorkerStart回调都自动创建一个协程
$pool->set([
    'enable_coroutine' => true, 
]);
$pool->on('workerStart', function ($pool, $id) {
    var_dump(date("c") . " \$id: $id, pid:" . getmypid());
    Coroutine::sleep(mt_rand(1,3));
});
$pool->start();
