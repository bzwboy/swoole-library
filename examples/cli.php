<?php
use Swoole\Event;

$fp = stream_socket_client('tcp://127.0.0.1:9501');
$data = str_repeat('A', 1024 * 1024*2);

Event::add($fp, function($fp) {
     echo fread($fp);
});

Event::write($fp, $data);
