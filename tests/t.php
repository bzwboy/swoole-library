<?php
$arr = [
    'a' => 1,
    'b' => 2,
];
var_dump($arr['c'] ?? 'undefined');
var_dump($arr['c']);
