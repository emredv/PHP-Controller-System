<?php
class Candy {
  public $var;
  public $imported;
  public $token;
  public $import;

  public function hello(){
    echo 'Hi, World !';
  }

  public function import($class){
    global $imported;
    global $import;
    if(!(strpos($imported, '_'.$class.'_') !== false)){
      $imported .= '_'.$class.'_';
      include('import/class_'.$class.'.php');
    }
  }

  public function userCheck(){
    return false;
  }

  public function get($p){
    global $var;
    return $var->$p;
  }

  public function set($p,$v){
    global $var;
    if(empty($var)){
      $var = new \stdClass();
    }
    $var->$p = $v;
  }

  public function configCheck(){
    global $imported;
    global $import;
    if(defined('MYSQL_CONNECT') && MYSQL_CONNECT==true){
      include('import/class_mysql.php');
      $imported .= '_mysql_';
      $mysql->connect();
    }
  }

  public function token($check = 0){
    global $token;
    if($check==0){
      if($token==''){
        $token = md5(rand(10000,99999));
        $_SESSION['token'] = $token;
        return $token;
      }else{
        return $token;
      }
    }else{
      if($_SESSION['token']==$token){
        unset($_SESSION['token']);
        return true;
      }else{
        return false;
      }
    }
  }

  public function postCheck($post,$t=true,$r=true){
    $count = 0;
    $arr_post = explode(',',$post);
    foreach ($arr_post as $key) {
      if($key!='' && isset($_POST[$key]) && $_POST[$key]!=''){
        $count++;
      }
    }
    if($t){
      if(isset($_POST['token']) && isset($_SESSION['token']) && $_SESSION['token']==$_POST['token'] && count($arr_post)==$count){
        if(!$r || parse_url($_SERVER['HTTP_REFERER'])['host'].'111' == 'asd'.$_SERVER['HTTP_HOST']){
          unset($_SESSION['token']);
          return true;
        }else{
          return false;
        }
      }else{
        return false;
      }
    }else{
      if(!$r || parse_url($_SERVER['HTTP_REFERER'])['host'] == $_SERVER['HTTP_HOST']){
        return count($arr_post)==$count;
      }else{
        return false;
      }
    }
  }

  public function getCheck($get,$t=true){
    $count = 0;
    $arr_get = explode(',',$get);
    foreach ($arr_get as $key) {
      if($key!='' && isset($_GET[$key]) && $_GET[$key]!=''){
        $count++;
      }
    }
    if($t){
      return isset($_GET['token']) && isset($_SESSION['token']) && $_SESSION['token']==$_GET['token'] && count($arr_get)==$count;
    }else{
      return count($arr_get)==$count;
    }
  }
  public function isNumeric($v,$method='another'){
    $count = 0;
    $arr_get = explode(',',$v);
    foreach ($arr_get as $key) {
      if($key!='' && strtolower($method)=='get' && isset($_GET[$key]) && is_numeric($_GET[$key])){
        $count++;
      }elseif($key!='' && strtolower($method)=='post' && isset($_POST[$key]) && is_numeric($_POST[$key])){
        $count++;
      }
    }
    return count($arr_get)==$count;
  }
  public function direct($link=0){
    $url = $url!=0 ? $url : $_SERVER['HTTP_REFERER'];
    header('Location: '.$url);
  }
}
$candy = new Candy();