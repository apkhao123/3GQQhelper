<?php
require("init.php");
require("libHTTP.php");
$sth=$db->query("SELECT * FROM `QQ` WHERE 1=1 ");
#$row=$sth->fetch();
$last=time();
while($row=$sth->fetch()) {
    $http=new http;
    $http->open("http://q16.3g.qq.com/g/s?sid={$row[sid]}&aid=nqqGroup&g_f=1657&g_ut=1&gutswicher=2");
    $http->set("Referer","http://q16.3g.qq.com/g/s?aid=nqqchatMain&g_ut=1&gutswicher=2&sid={$row[sid]}&myqq={$row[qq]}");
    $http->set("X-Real-Ip","119.147.225.167");
    $http->set("X-Forwarded-For","59.59.30.90, 119.147.225.167");
    $http->send();
    $http->get();
    $n=0;
            file_put_contents("log/qq-{$row[qq]}-{$n}.html",$http->return["body"]);
    while(true) {
        if($n>3) {
            break;
        }
        elseif(preg_match('#正在跳转<br/>如果不能自动跳转请点击<a href="([^"]+)">这里</a><br/>#',$http->return["body"],$r)) {
            $http->open(str_replace("&amp;","&",$r[1]));
            $http->set("Referer","http://q16.3g.qq.com/g/s?sid={$row[sid]}&aid=nqqGroup&g_f=1657&g_ut=1&gutswicher=2");
            $http->set("X-Real-Ip","119.147.225.167");
            $http->set("X-Forwarded-For","59.59.30.90, 119.147.225.167");
            $http->send();
            $http->get();
            file_put_contents("log/qq-{$row[qq]}-{$n}.html",$http->return["body"]);
        }
        elseif(preg_match('#The URL has moved <a href="([^"]+)">here</a>#',$http->return["body"],$r)) {
            $http->open(str_replace("&amp;","&",$r[1]));
            $http->set("Referer","http://q16.3g.qq.com/g/s?sid={$row[sid]}&aid=nqqGroup&g_f=1657&g_ut=1&gutswicher=2");
            $http->set("X-Real-Ip","119.147.225.167");
            $http->set("X-Forwarded-For","59.59.30.90, 119.147.225.167");
            $http->send();
            $http->get();
            file_put_contents("log/qq-{$row[qq]}-{$n}.html",$http->return["body"]);

        }
        elseif(strstr($http->return["body"],"系统繁忙，请稍候再试<br/>")) {
            $db->exec("UPDATE `QQ` SET `state`='0',`last`='{$last}' WHERE `qq`='{$qq}' ");
            break;
        }
        elseif(strstr($http->return["body"],"&amp;aid=nqqChat&amp;")){
            $db->exec("UPDATE `QQ` SET `last`='{$last}' WHERE `qq`='{$qq}' ");
            break;
        }
        else {
            break;
        }
        $n++;
    }


}
?>














