<?php
#! /usr/local/bin/php
/*
Author: Erik dev
Note: This script is free. .
Property of: https://github.com/tkiredev
*/
CLASS _e{
 //services available:
 public static $services = [
 "cloudflare",
 "CloudFront","cloudfront"];
 //ports available:
 public static $ports = [];
 private static $server = "";
 private static $error_msg = "";
 public static function c_vpn($urid){
  $patch ="config.json";
  $domains = [];
  if(file_exists($patch)){
   foreach((json_decode(file_get_contents($patch),0))->domains AS $k => $u){
    array_push($domains,[$k=>$u]);
   }
  }else return !1;

 function c_l($urid,$domain){

  curl_setopt_array($c = curl_init(),[
   CURLOPT_URL => $urid,
   CURLOPT_PORT => 80,
   CURLOPT_HEADER => 0,
   CURLOPT_RETURNTRANSFER => 0,
   CURLOPT_NOBODY => 0,
   CURLOPT_HTTPHEADER => [
   "Host:${domain}",
   "Connection:upgrade",
   "Upgrade:websocket"
   ],
  ]);
  return [curl_exec($c),curl_getinfo($c)];
  curl_close($c);
 }
 
 function cf($sv,$urid,$domains){
  foreach ($domains as $key => $value) {
    foreach($value as $k => $dmain){
      if (preg_match("/^{$k}/i",$sv)) {
      c_l($urid,$dmain);
      break;
      }
    }
  }
  
 };

  switch (self::$server) {
    case 'cloudflare':
    cf('cloudflare',$urid,$domains);
     break;
    case 'Cloudfront'  || 'Google Frontend' || 'AmazonS3' || 'tsa_p':
    cf('cloudfront',$urid,$domains);
     break;
  };
 
  //return !1;//(curl_getinfo($c))['http_code'];
}
 
 public static function _call($urid,$type,$port){
 curl_setopt_array($c = curl_init(),[
  CURLOPT_URL => $urid,
  CURLOPT_RETURNTRANSFER => 1,
  CURLOPT_SSL_VERIFYPEER => true,
  CURLOPT_HEADER => 1,
  CURLOPT_MAXREDIRS => 3,
  CURLOPT_FOLLOWLOCATION => false,
  CURLOPT_NOBODY => true,
  CURLOPT_PORT => $port,
  CURLOPT_TIMEOUT => 20,
  ]);
  
  $aws = [];
  $_s = curl_exec($c);
  if (!$_s || (curl_error($c)) ){
    self::$error_msg = curl_error($c);
    return !1;
  } 

  $inf = curl_getinfo($c);

  if ($inf["primary_port"] === 443  || $inf["primary_port"] === 80) {
    array_push(self::$ports,$inf["primary_port"]);
  }
  

   if($type){
  //GET HEADERS THE HOST
  if( count($x = explode("\n\r",$_s)) > 2){
   unset($x[(count($x))-1],$x[0]); //1
  }else $x[1] = $x[0];unset($x[0]);
  
  $h = explode("\n",ltrim($x[1]));

  //custom header
  $customH = false;

  $match = function($v,$n){
   $regex = ["Server: ","HTTP","Connection:","Content-Type:"];
    return preg_match("/^{$regex[$n]}/i",$v);
   };

   foreach($h as $k => $value){
    //save server response
    if($match($value,0)){
      $server = (trim(str_ireplace("Server: ", "", $value)));
      
      self::$server = ($server);
    }

    if ($customH) {
     if($match($value,0)||$match($value,1)||$match($value,2)){
      array_push($aws,$value);
     }
    }else(array_push($aws,$value));
   }
   array_push($aws,(("IP primary: ").$inf['primary_ip']).(":").($inf['primary_port']));
   return($aws);
  } else return($inf)['http_code'];
  
  //close resources:
  curl_close($c);
 }
 public static function c($v,$text){
 //colors:
 //red: 31
 //black: 30
 //green: 32
 //blue: 34
 //yellow: 33
 //white: 37
 //magenta: 35
 //cyan: 36
 return "\e[1;{$v}m{$text}\e[0m";
 }
 public static function _dep($urid){
 $schemes = ["http://","https://"];
 $headers = self::_call($urid,1,false);

 if(!$headers){
  $headers = self::_call($schemes[1].$urid,1,443);
  array_push($headers,array_unique(self::$ports));
  if (!$headers) return !1;
 }else{
  $ssl_open = self::_call($urid,1,443);
  array_push($headers,array_unique(self::$ports));
 }

 return $headers;
 }
 
 public static function r_ik($input){
  //Clear screen user and hidden errors:
  system('clear');
  //error_reporting(0);
  //Start script:
  print self::c(34,"\nBy:KireDev\n");
  //Warning:
  print self::c(33,"\n[!] Warning: Use este script con datos mobiles o un plan de redes.\n");
  //Require Input:
  $ouput = self::_dep($input);
  
  //server failed in request
  if (!$ouput) {
    $er = self::$error_msg;
    print self::c(31,"\n[X] Error ${er}\n\n");
    return !1;  
  }
  //Response ports open:
  print self::c(36,"\n[-] Ports:\n");
  $p_ = count($ouput) - 1;

  $ports = array_slice($ouput,$p_);
  unset($ouput[$p_]);
  
  print "\x20\x20".self::c(33,"| WEB | ").($ports[0][0] === 80?self::c(32,"OPEN -> 80"):self::c(31,"CLOSE"));

  print "\n\x20\x20".self::c(33,"| SSL | ").( isset($ports[0][1]) && $ports[0][1] === 443 || $ports[0][0] === 443?self::c(32,"OPEN -> 443"):self::c(31,"CLOSE"));

  

  //Response all headers
  print self::c(36,"\n\n[-] Response Host Headers:\n");
  
  foreach($ouput AS $k => $v){
    print ("\x20\x20").$v.("\n"); 
  }

  //support connection:
  $serv = function($service){
   return (preg_match("/^{$service}/i",self::$server))?32:90;
  };
  
  //print icon >
  $ic=function($i){return($i===32?"> ":"\x20");};
  
  print self::c(36,"\n[-] Supported connection:\n");
  print self::c(($i=$serv("CloudFront")),"\x20{$ic($i)}CloudFront\n");
  print self::c(($i=$serv("CloudFlare")),"\x20{$ic($i)}CloudFlare\n");

  //check connecition?:
  if (
    self::$server == "cloudflare" ||
    self::$server == "cloudfront" ||
    self::$server == "CloudFront" ||
    self::$server == "AmazonS3" ||
    self::$server == "Google Frontend") {
   $svr = self::$server;
   print self::c(33,"\nConnecting with ${svr}...\n");
   sleep(5);
   self::c_vpn($input);
  } else print "\n";
  
 }
}
//URL ALOW:
$argument = (count($c = getopt("h:")) < 1)? (isset($argv[1]))?(false):(false):(is_array($c['h'])?(false):$c['h']);

($argument!=false)?($x =_e::r_ik($argument)):
  print _e::c(31,"¿URL?\n");
//--------------END-------------------
