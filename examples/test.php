<?php
$atomic = new Swoole\Atomic();

$serv = new Swoole\Server('127.0.0.1', '9501');
$serv->set([
    'worker_num' => 1,
    'log_file' => '/dev/null'
]);
$serv->on("start", function ($serv) use ($atomic) {
    var_dump(__FILE__ . __LINE__, $atomic->get());
    if ($atomic->add() == 2) {
    var_dump(__FILE__ . __LINE__, $atomic->get());
        $serv->shutdown();
    }
});
$serv->on("ManagerStart", function ($serv) use ($atomic) {
    var_dump(__FILE__ . __LINE__, $atomic->get());
    if ($atomic->add() == 2) {
    var_dump(__FILE__ . __LINE__, $atomic->get());
        $serv->shutdown();
    }
});
$serv->on("ManagerStop", function ($serv) {
    echo "shutdown\n";
});
$serv->on("Receive", function () {
});
$serv->start();
