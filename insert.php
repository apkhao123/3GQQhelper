<?php
require("init.php");
require("libHTTP.php");
if(isset($_POST["verify"])) {
    $http=new http;
    $http->open($_GET["url"]);
    foreach($_POST as  $key=>$value) {
        $http->post($key,$value);
    }
    $http->send();
    $http->get();
    $loginResult=$http->return["body"];

}
elseif(!preg_match("#^\d{5,11}$#",$_POST["qq"])) {
    header('Location: index.php?tip='.urlencode("输入QQ不合法"));
    exit();
}
elseif($_POST["pwd"]==null) {
    header('Location: index.php?tip='.urlencode("输入密码不能为空"));
    exit();
}
else {
    $http=new http;
    $http->open("http://pt.3g.qq.com/handleLogin?g_ut=2&sid=ASOt2C-h_kVc1hDrt5EvSdR0&vdata=BC4818CF34A6DB885B5D05876A3A473B");
    $http->post("login_url","http://pt.3g.qq.com/s?g_ut=2&aid=nLogin");
    $http->post("sidtype","1");
    $http->post("nopre","0");
    $http->post("q_from","");
    $http->post("loginTitle","手机腾讯网");
    $http->post("bid","0");
    $http->post("qq",$_POST["qq"]);
    $http->post("pwd",$_POST["pwd"]);
    $http->post("loginType","1");
    $http->post("loginsubmit","登录");
    $http->send();
    $http->get();
    $loginResult=$http->return["body"];
}


if(!isset($loginResult) or $loginResult=="") {
} elseif(strstr($loginResult,"您输入的帐号或密码不正确，请重新输入。")) {
    header('Location: index.php?tip='.urlencode("您输入的帐号或密码不正确，请重新输入。"));
    exit();
}
elseif(strstr($loginResult,"系统检测到您的操作异常，为保证您的号码安全，请输入验证码进行验证，防止他人盗取密码。<br/>")) {
    preg_match('#<img src="([^"]+)" alt="验证码"/>#',$loginResult,$verifyImg);
    file_put_contents("verify.gif",file_get_contents($verifyImg[1]));
    $array=explode("</form>",$loginResult);
    $html=$array[1];
    preg_match('#<form action="([^"]+)" method="post">#',$html,$array);
    $url="http://pt.3g.qq.com".$array[1];
    $url="insert.php?qq=".$_POST["qq"]."&pwd=".$_POST["pwd"]."&url=".urlencode($url);
    preg_match_all('#<input type="hidden" name="([^"]+)" value="([^"]+)"/>#',$html,$array);
    $htmlOut="<form action=\"{$url}\" method=\"post\">\n";
    foreach($array[1] as $key=>$name) {
        $value=$array[2][$key];
        $htmlOut.="<input type=\"hidden\" name=\"{$name}\" value=\"{$value}\"/>\n";
    }
    include("header.php");
    $htmlOut.=<<<HTML
              <div class="bar">CDD腾讯QQ助手</div>
                             <div class="tip">系统检测到您的操作异常，为保证您的号码安全，请输入验证码进行验证，防止他人盗取密码。</div>
                                            请输入上图字符(不区分大小写)：<br/>
                                            <input name="verify"  type="text" maxlength="18" value="" /><br/>
                                                    <input type="submit" name="submitlogin" value="马上登录"/>
                                                            </form>
HTML;
    echo "<img src=\"verify.gif?r=".rand(10000,99999)."\" alt=\"验证码\"/><br/>";
    echo $htmlOut;
}
elseif(preg_match('#The URL has moved <a href="([^"]+)">here</a>#',$loginResult,$sid)) {
    $http=new http;
    $http->open($sid[1]);
    $http->send();
    $http->get();
    preg_match("#sid=(\w+)&#",$sid[1],$sid);
    $sid=$sid[1];
    if(isset($_GET["qq"])) {
        $qq=$_GET["qq"];
        $pwd=$_GET["pwd"];
    } else {
        $qq=$_POST["qq"];
        $pwd=$_POST["pwd"];
    }
    $var=$db->query("SELECT COUNT(*)  as `count` FROM `QQ` WHERE `qq`='{$qq}' ")->fetch();
    if($var["count"]>0) {
        $last=time();
        $db->exec("UPDATE `QQ` SET `pwd`='{$pwd}', `sid`='{$sid}',`last`='{$last}' WHERE `qq`='{$qq}' ");
        header('Location: index.php?tip='.urlencode("更新QQ成功!"));
        exit();
    } else {
        $db->exec("INSERT INTO `QQ`(`qq`,`pwd`,`sid`,`last`) VALUES('{$qq}','{$pwd}','$sid','".time()."')");
        header('Location: index.php?tip='.urlencode("添加QQ成功!"));
        exit();
    }

}
else {
    file_put_contents("result-".time().".html",$loginResult);

    include("header.php");
    $htmlOut.=<<<HTML
              <div class="bar">CDD腾讯QQ助手</div>
                             <div class="wrong">
                                            未知错误，等待管理员处理。
                                            </div>
HTML;
}





require("footer.php");