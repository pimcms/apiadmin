<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
use Curl\Curl;
// 应用公共文件
/**
 * http请求
 * @param $url
 * @param array $data
 * @param string $type
 * @return null
 */
function http_curl($url, $data=[], $type="get"){
    $curl = new Curl();
    switch ($type){
        case "get":
            $curl->get($url, $data);
            break;
        case "post":
            $curl->post($url, $data);
    }
    if ($curl->error){
        echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
        exit;
    }
    $res = $curl->response;

    return $res;
}

function makeId($length, $time = true, $char = false)
{
    if ($time) {
        return time() . randomString($length, $char);
    } else {
        return randomString($length, $char);
    }
}
function randomString($length = 32, $char = false)
{
    if ($char) {
        $str = "a0b1c2d3eZ4YfX5WgV6UhT7SiR8QjP9NkMzLlKymJxInHwGpFvEqDuCrByAs";
    } else {
        $str = "0123456789";
    }
    $result = "";
    for ($i = 0; $i < $length; $i++) {
        $result .= $str[rand(0, strlen($str) - 1)];
    }
    return $result;
}
