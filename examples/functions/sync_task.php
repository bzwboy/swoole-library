<?php

/**
 * 执行异步任务 (Task)
 * https://wiki.swoole.com/#/start/start_task
 */
$serv = new Swoole\Server('127.0.0.1', 9501);

//设置异步任务的工作进程数量
$serv->set([
    'task_worker_num' => 4
]);

//此回调函数在worker进程中执行
$serv->on('Receive', function ($serv, $fd, $reactor_id, $data) {
    // sleep($d = mt_rand(1, 1));
    $d = 0;

    //投递异步任务
    $task_id = $serv->task($data . mt_rand(1, 10));
    echo date('c') . " d[$d] Dispatch AsyncTask: id={$task_id}\n";

    //投递异步任务
    $task_id = $serv->task($data . mt_rand(1, 10));
    echo date('c') . " d[$d] Dispatch AsyncTask: id={$task_id}\n";

    //投递异步任务
    $task_id = $serv->task($data . mt_rand(1, 10));
    echo date('c') . " d[$d] Dispatch AsyncTask: id={$task_id}\n";
});

//处理异步任务(此回调函数在task进程中执行)
$serv->on('Task', function ($serv, $task_id, $reactor_id, $data) {
    // sleep($d = mt_rand(1, 10));
    $d = 0;

    echo date('c') . " d[$d] New AsyncTask[id={$task_id}]" . PHP_EOL;
    //返回任务执行的结果
    $serv->finish("{$data} -> OK");
});

//处理异步任务的结果(此回调函数在worker进程中执行)
$serv->on('Finish', function ($serv, $task_id, $data) {
    echo date('c') . " AsyncTask[{$task_id}] Finish: {$data}" . PHP_EOL;
});

$serv->start();
