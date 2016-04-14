<?php
    $token = 'kaikai';
    $timestamp = $_GET['timestamp'];
    $nonce = $_GET['nonce'];
    $tmpstr = array($timestamp, $nonce, $token);
    sort($tmpstr);
    $tmp = implode('', $tmpstr);
    $tmp = sha1($tmp);
    $signature = $_GET['signature'];
    $echostr = $_GET['echostr'];
    if($signature == $tmp){
        echo $echostr;
        exit;
    }