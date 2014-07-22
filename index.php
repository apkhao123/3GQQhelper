<?php
include("header.php");

echo <<<HTML
<div class="bar">CDD腾讯QQ助手</div>
<div class="tip">为QQ用户提供免费挂机服务，我们不会保存用户的账号和密码，我们使用高质量的保存技术为保护你的信息</div>
HTML;
if(isset($_GET["tip"])){
echo <<<HTML
<div class="wrong">{$_GET["tip"]}</div>
HTML;

}
echo <<<HTML
<div class="form">
<form action='insert.php' method='post'>
<div class="input">Q Q <input type='text' name='qq' value="" /></div>
<div class="input">密码<input type='password' name='pwd' value='' /></div>

<div style="height:40px;background:#FFF;color:#999;border:#999 1px solid;line-height:40px;padding-left:10px;font-size:25px;margin:8px;">登录方式<select name='loginType' style="display:inline-block;width:100px;background:transparent ;margin:0px;height:38px;border-style:none none none solid;border-color:#999;">
<option value='1'>在线</option>
<option value='2'>隐身</option>
</select></div>
<div class="submit"><input type="submit"   name='action' value='Go'/></div>
</form>
</div>
<div class="tip">如果你觉得我们的服务很好，你也可以分享给你的小伙伴哦！我们的网址:<a href=/>http://QQ.GJB.PW</a></div>
<div style="color:#666;background:#99FFCC;padding:6px;">已有<big style="color:red;">很多人</big>次使用了我们的挂机服务</div>
HTML;
include("footer.php");
?>















