<?php

/**
 * mt_rand 随机数
 * https://wiki.swoole.com/#/getting_started/notice
 */

mt_rand(0, 1);

//开始
$worker_num = 6;

//fork 进程
for($i = 0; $i < $worker_num; $i++) {
    $process = new Swoole\Process('child_async', false, 2);
    $pid = $process->start();
}

//异步执行进程
function child_async(Swoole\Process $worker) {
    mt_srand(); //重新播种
    echo mt_rand(0, 100).PHP_EOL;
    $worker->exit();
}
