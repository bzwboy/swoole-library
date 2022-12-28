<?php

/**
 * 在协程中捕获运行时异常 / 错误
 * https://wiki.swoole.com/#/getting_started/notice?id=%e5%9c%a8%e5%8d%8f%e7%a8%8b%e4%b8%ad%e6%8d%95%e8%8e%b7%e8%bf%90%e8%a1%8c%e6%97%b6%e5%bc%82%e5%b8%b8%e9%94%99%e8%af%af
 */

use Swoole\Coroutine;
use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;

set_error_handler(function ($errno, $errstr) {
    throw new ErrorException($errstr, $errno);
}, E_ALL);

run(function () {
    var_dump(__FILE__ . __LINE__);
    go(function () {
        try {
            call_user_func($func);
        } catch (Error $e) {
            var_dump(__FILE__ . __LINE__, $e);
        } catch (Exception $e) {
            var_dump(__FILE__ . __LINE__, $e);
        } catch (Throwable $e) {
            var_dump(__FILE__ . __LINE__, $e);
        } catch (ErrorException $e) {
            var_dump(__FILE__ . __LINE__, $e);
        }
    });

    //协程1的错误不影响协程2
    var_dump(__FILE__ . __LINE__);
    go(function () {
        Coroutine::sleep(2);
        echo "Delay 5s... and output: ", 2;
    });
});
