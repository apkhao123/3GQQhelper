<?php
/*
* libHTTP.php - HTTP请求类库
* 一款封装的http请求类库，可以用于模拟任何浏览器请求
* by 小志 
* Email:apkhao123@gmail.com
* 
* 以上内容禁止修改!!!
*/
class http{
var $header=array("User-agent"=>"TTMobile/09.03.18/symbianOS9.1 Series60/3.0 Nokia6120cAP3.03","Accept-language"=>"zh-CN","Accept"=>"text/html");
var $post=array();
var $cookie=array();
var $url=array("method"=>"GET");
var $connect=false;
var $error=null;
var $query=null;
var $log=null;
var $send=false;
var $return=array();
function method($t){
$t=strtoupper($t);
if(!in_array($t,array("GET","POST","HEAD","DELETE","TRACE")))
return false;
$this->url["method"]=$t;
return true;
}
function cookie($key,$value=null){
if(is_array($key)){
foreach($key as $k=>$v)
$this->cookie($k,$v);
return true;
}
if(is_string($key)){
if(preg_match("#^\w+$#",$key)){
$this->cookie[$key]=$value;
return true;
}
$tag=$this->readTags($key);
if(is_array($tag)){
foreach($tag as $k=>$v){
$this->cookie($k,$v);
}
return false;
}
return false;
}
return false;
}
function post($key,$value=null){
if(is_array($key)){
foreach($key as $k=>$v)
$this->post($k,$v);
return true;
}
if(is_string($key) && $value!=null){
if(preg_match("#^\w+$#",$key)){
$this->post[$key]=$value;
return true;
}
$tag=$this->readTags($key);
if(is_array($tag)){
foreach($tag as $k=>$v){
$this->post($k,$v);
}
return true;
}
return false;
}
return false;
}
function set($key,$value=null){
if(is_array($key)){
foreach($key as $k=>$v)
$this->set($k,$v);
return true;
}
if(!is_string($key))return false;
if(preg_match("#^[a-zA-Z0-9-_]+$#",$key) && $value!=null){
$key=strtolower($key);
$key=ucwords($key);
$this->header[$key]=$value;
return true;
}
$tag=$this->readTags($key) or parse_str($key);
if(is_array($tag)){
foreach($tag as $k=>$v){
$this->set($k,$v);
}
return true;
}
return false;
}
function open($url){

$this->header=array("User-agent"=>"TTMobile/09.03.18/symbianOS9.1 Series60/3.0 Nokia6120cAP3.03");
$this->post=array();
$this->cookie=array();
$this->url=array("method"=>"GET");
$this->connect=false;
$this->error=null;
$this->query=null;
$this->log=null;
$this->send=false;
$this->return=array();

$url=parse_url($url);
if(!is_array($url))
return false;
if(!isset($url["host"]))
return false;
if(isset($url["scheme"])&&$url["scheme"]!="http")
return false;
if(!isset($url["port"]))
$url["port"]=80;
if(!isset($url["path"]))
$url["path"]="/";
$url["method"]=$this->url["method"];
$this->url=$url;
extract($this->url);
if(!@$this->connect=fsockopen($host,$port,$erron,$errstr,30)){
$this->error[]=$errstr;
return false;
}
return true;
}
function send(){
if(!$this->connect){ echo "Connect Error!";
return false;
}
extract($this->url);
if(count($this->post)>0)
$method="POST";
if(isset($query))
$path.="?".$query;
$query="{$method} {$path} HTTP/1.1\r\n";
if($port!=80)
$host.=":".$port;
$query.="HOST: {$host}\r\n";
if(!isset($this->header["Content-type"]))
$this->header["Content-type"]="application/x-www-form-urlencoded";
if(isset($this->header["Connection"]))
 unset($this->header["Connection"]);
if(isset($this->header["Content-length"]))
 unset($this->header["Content-length"]);
if(count($this->header)>0){
foreach($this->header as $key=>$value){
$query.="{$key}: {$value}\r\n";
}
}
if(count($this->cookie)>0){
$query.="Cookie:";
foreach($this->cookie as $key=>$value){
$value=urlencode($value);
$query.=" {$key}={$value};";
}
$query.="\r\n";
}
$post=null;
if(count($this->post)>0){
foreach($this->post as $key=>$value){
$value=urlencode($value);
$post.="&{$key}={$value}";
}
$post=substr($post,1);
$query.="Content-Length: ".strlen($post)."\r\n";
}
$query.="Connection: close\r\n\r\n".$post;
#var_dump($query);
if(!fputs($this->connect,$query)){
#echo "Put error!";
return false;
}
$this->query=$query;
$this->send=true;
return true;
}
function close(){
if($this->connect)
return fclose($this->connect);
return true;
}
function get(){
if(!$this->send or !$this->connect){
#echo "Please call send";
return false;
}
#读取第一�??
$httpStatus=trim($this->getString($this->connect,4096));
$header=array();
while($str=trim($this->getString($this->connect,4096)))
{
$start=strpos($str,":");
$end=$start+1;
$key=strtolower(trim(substr($str,0,$start)));
$value=trim(substr($str,$end));
		if($key=="set-cookie"){
			$header["set-cookie"][]=$value;
		}else{
			$header[$key]=$value;
		}
	}
	$this->return["header"]=array_change_key_case($header);
$this->return["header"]["status"]=explode(" ",$httpStatus);
$this->return["header"]["status"]=$this->return["header"]["status"][1];
	$body=null;
	if(isset($this->return["header"]["content-length"])){
		$i=0;
		$length=$this->return["header"]["content-length"];
		while(!feof($this->connect)&&$i<$length){
			$str=$this->getString($this->connect);
			$body.=$str;
			$i+=strlen($str);
		}
	}elseif(isset($this->return["header"]["transfer-encoding"]) && $this->return["header"]["transfer-encoding"]=="chunked"){
		$chunkSize=(integer)hexdec(trim($this->getString( $this->connect,4096)) );
		while(!feof($this->connect) && $chunkSize>0) {
		$i=0;
		while(!feof($this->connect)&&$i<$chunkSize){
			$str=$this->getString($this->connect);
			$i+=strlen($str);
			$body.=$str;
		}
		$chunkSize=(integer)hexdec(trim($this->getString( $this->connect,4096)) );
		}
	}else{
		while(!feof($this->connect)){
			$str=$this->getString($this->connect);
			$body.=$str;
		}
	}
$this->return["body"]=$body;
$this->parseCookie();
$this->close();
return true;
}
function getString($f,$length=2048){
$get=fgets($f,$length);
if($get)
$this->log.=$get;
return $get;
}
function parsecookie(){
if(!isset($this->return["header"]["set-cookie"])){
$this->return["cookie"]=array();
return true;
}
foreach($this->return["header"]["set-cookie"] as $row){
$start=strpos($row,"=")-1;
$end=strpos($row,";")-1;
if(!$start or !$end) continue;
$key=trim(substr($row,0,$start));
$value=substr($row,$start,$end);
$this->return["cookie"][$key]=$value;
}
return true;
}
function readtags($code,&$i=0){
 $length=strlen($code);
 if($length==0)
 return true;
 $tags=array();
 $key=null;
 $value=null;
 $w="key";
  for($i=(int)$i;$i<$length;$i++){
    if($code[$i]=="\""){
      $continue=false;
      $t=null;
      for($i=++$i;$i<$length;$i++){
        if($code[$i]=="\\"){
          $continue=true;
          $t.=$code[$i];
        }elseif($continue){
          $continue=false;
          $t.=$code[$i];
        }elseif($code[$i]== "\""){
          if($w=="key"){
          $key=$t;
          }else{
          $value=$t;
          $w="key";
          }
          $t=null;
          break;
        }else{
          $t.=$code[$i];
        }
      }
    }elseif($code[$i]=="'"){
      $continue=false;
      $t=null;
      for($i=++$i;$i<$length;$i++){
        if($code[$i]=="\\"){
          $continue=true;
          $t.=$code[$i];
        }elseif($continue){
          $continue=false;
          $t.=$code[$i];
        }elseif($i==($length-1)&&$code[$i]!= "'"){
        return false;
        }elseif($code[$i]== "'"){
          if($w=="key"){
            $key=$t;
          }else{
            $value=$t;
            $w="key";
          }
          $t=null;
          break;
        }else{
          $t.=$code[$i];
        }
      }
    }elseif($i==($length-1)){
    if($w!="value")
    return false;
    $value.=$code[$i];
    $tags[$key]=$value;
    }elseif($code[$i]==" " or $code[$i]=="&"){
      if($key==null or $value==null)
      continue ;
      $tags[$key]=$value;
      $key=null;
      $value=null;
      $w="key";
    }elseif($this->isVariable ($code[$i])){
      if($w=="key")
      $key.=$code[$i];
      else
      $value.=$code[$i];
    }elseif($w=="key" && $code[$i]=="="){
      $w="value";
    }elseif($w=="key"){
    return false;
      break;
    }else{
      $value.=$code[$i];
    }
  }
  return $tags;
}
function isVariable($txt){
$ascii=ord($txt[0]);
#数字
  if($ascii>=48 && $ascii<=57 )
return true;
#大写字母
  if($ascii>=65 && $ascii<=90 )
return true;
#小写字母
  if($ascii>=97 && $ascii<=122 )
return true;
  if($ascii>=127 && $ascii<=255 )
return true;
return false;
}

}