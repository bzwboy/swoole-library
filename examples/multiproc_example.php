<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/1 0001
 * Time: 22:16
 */

$citys = [
    'luohuqu', 'a', 'b'
];

$start_time = time();

//$citys = ['luohuqu','futianqu'];
$page = 3;

//循环城市创建多进程，使用消息队列
foreach ($citys as $key => $city) {
    $process = new Swoole\Process(function ($worker) use ($city, $page) {
        //循环分页数
        for ($i = 1; $i <= $page; $i++) {
            //创建分页地址
            $url = 'https://sz.lianjia.com/zufang/' . $city . '/pg' . $i;
            //爬取网页html数据
            $data = getUrlData($url);
            sleep(mt_rand(1, 3));
            //往队列放入数据
            $worker->push(json_encode($data, JSON_UNESCAPED_UNICODE));
        }
    });
    //使用队列
    $process->useQueue();
    //开启进程获取进程id
    $pid = $process->start();
    $pid = $process->pid;
    // echo $pid . PHP_EOL;
    //赋值进程数组
    $workers[$pid] = $process;
}

//循环进程数组取出队列，使用协程将数据插入表
foreach ($workers as $pid => $worker) {
    for ($i = 1; $i <= $page; $i++) {
        $data = json_decode($worker->pop(), true);
        //三种方式，任意一种即可

        //协程容器里面开启协程，短名称特性，需要在php.ini设置swoole.use_shortname='on'
        /*         Co\run(function () use ($data) {
            go(function () use ($data) {
                mysql_query($data);
            });
        }); */

        //协程容器（对Scheduler的封装），短名称特性，需要在php.ini设置swoole.use_shortname='on'
        Co\run(function () use ($data, $pid) {
            // mysql_query($data);
            var_dump(date('c') . ' pid:' . $pid . ' data:' . json_encode($data));
        });

        //        //协程调度器类
        //        $scheduler = new Swoole\Coroutine\Scheduler();
        //        $scheduler->add(function() use($data){
        //            mysql_query($data);
        //        });
        //        $scheduler->start();
    }
}

//执行协程mysql客户端
function mysql_query($data)
{
    /*     //创建mysql连接
    $mysql = new Swoole\Coroutine\MySQL();
    $mysql->connect([
        'host'=>'127.0.0.1',
        'port'=>3306,
        'user'=>'root',
        'password'=>'cxh1002.',
        'database'=>'lianjia',
    ]);
    $time = time();
    foreach ($data as $val){
        //预处理语句
        $stmt = $mysql->prepare('INSERT INTO house (title,address,area,aspect,house_type,price,add_time) VALUES (?,?,?,?,?,?,?)');
        if(!$stmt || $stmt->error){
            var_dump($mysql->error);
            return;
        }
        //发送预处理数据参数
        $res = $stmt->execute([
            $val['title'],
            $val['address'],
            $val['area'],
            $val['aspect'],
            $val['house_type'],
            $val['price'],
            $time,
        ]);
//        var_dump($res);
    } */
    var_dump(date('c') . ' data:' . json_encode($data));
}

//爬取网页数据
function getUrlData($url)
{
    /*     $data = [];
    //获取整个网页html
    $html = file_get_contents($url);
    //匹配某个div数据块
    $preg_div = '/<div class=\"content__list--item--main\">.*?<\/div>/ism';
    preg_match_all($preg_div,$html,$match_div);
    //循环匹配数据存入数据库
    foreach ($match_div[0] as $key=>$val){
        //匹配标题，地址
        $preg_a = '/<a .*?>.*?<\/a>/ism';
        preg_match_all($preg_a,$val,$match_a);
        if(count($match_a[0]) < 4) continue;
        list($a,$b,$c,$d) = $match_a[0];
        $data[$key]['title'] = trim(strip_tags($a));
        $data[$key]['address'] = trim(strip_tags($b)) . '/' . trim(strip_tags($c)) . '/' . trim(strip_tags($d));
        //匹配面积，朝向，户型
        $preg_i = '/<\/i>.*?<i>/ism';
        preg_match_all($preg_i,$val,$match_i);
        if(count($match_i[0]) < 3) continue;
        list($e,$f,$g) = $match_i[0];
        $data[$key]['area'] = trim(strip_tags($e));
        $data[$key]['aspect'] = trim(strip_tags($f));
        $data[$key]['house_type'] = trim(strip_tags($g));
        //匹配月租
        $preg_em = '/<em>.*?<\/em>/ism';
        preg_match_all($preg_em,$val,$match_em);
        $data[$key]['price'] = trim(strip_tags($match_em[0][0]));
    } */

    return [
        'a' => mt_rand(0, 999),
        'b' => mt_rand(0, 999),
        'c' => mt_rand(0, 999),
        'd' => mt_rand(0, 999),
    ];
}

echo 'time:' . (time() - $start_time) . PHP_EOL;
