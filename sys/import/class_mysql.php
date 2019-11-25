<?php
class Mysql {
  public $conn;
  public $usercheck = 0;

  public function connect($db=0,$user=0,$pass=0,$server=0){
    global $conn;
    $db = $db==0 ? (defined('MYSQL_DB') ? MYSQL_DB : '') : $db;
    $user = $user==0 ? (defined('MYSQL_USER') ? MYSQL_USER : '') : $user;
    $pass = $pass==0 ? (defined('MYSQL_PASS') ? MYSQL_PASS : '') : $pass;
    $server = $server==0 ? (defined('MYSQL_SERVER') ? MYSQL_SERVER : '127.0.0.1') : $server;
    $conn = mysqli_connect($server, $user, $pass, $db);
    mysqli_set_charset($conn,"utf8");
    if (!$conn) {
        echo "Mysql connection error" . PHP_EOL;
        exit;
    }
    return $conn;
  }

  public function query($query,$b = true){
    global $conn;
    $sql = mysqli_query($conn, $query);
    $result->rows = mysqli_num_rows($sql);
    $result->fetch = mysqli_fetch_assoc($sql);
    if($b){
      return $result;
    }
  }

  public function loginCheck($arr,$t = true){
    global $conn;
    $result = new \stdClass();
    $query = '';
    foreach ($arr as $key => $value) {
      if($key!='table_user' && $key!='table_token'){
        $query .= $key.'="'.mysqli_real_escape_string($conn,$value).'" AND ';
      }
    }
    $query = '('.substr($query,0,-4).')';
    $sql_user = mysqli_query($conn, 'SELECT * FROM '.$arr['table_user'].' WHERE '.$query);
    if(mysqli_num_rows($sql_user)==1){
      $result->success = true;
      $result->fetch = mysqli_fetch_assoc($sql_user);
      $result->rows = mysqli_num_rows($sql_user);
      if($t){
        $token1 = md5(rand(0, 1000000));
        $token2 = md5($_SERVER['REMOTE_ADDR']);
        $token3 = md5($_SERVER['HTTP_USER_AGENT']);
        setcookie("token1", $token1, time() + 61536000, "/");
        setcookie("token2", $token2, time() + 61536000, "/");
        $sql_token = mysqli_query($conn, 'INSERT INTO '.$arr['table_token'].' (userid,token1,token2,token3,ip) VALUES ("' . $result->fetch['id'] . '","' . $token1 . '","' . $token2 . '","' . $token3 . '","'.$_SERVER['REMOTE_ADDR'].'")');
      }
        return $result;
    }else{
      $result->success = false;
      return $result;
    }
  }

  public function userCheck($fetch = true){
    global $conn;
    global $usercheck;
    if($usercheck==0){
    $result = new \stdClass();
    if(isset($_COOKIE['token1']) && isset($_COOKIE['token2'])){
      $token1 = mysqli_real_escape_string($conn, $_COOKIE['token1']);
      $token2 = mysqli_real_escape_string($conn, $_COOKIE['token2']);
      $token3 = md5($_SERVER['HTTP_USER_AGENT']);
      $sql_token = mysqli_query($conn, 'SELECT * FROM tb_token WHERE token1="'.$token1.'" AND token2="'.$token2.'" AND token3="'.$token3.'"');
      if(mysqli_num_rows($sql_token) == 1){
        if($fetch){
            $result->success = true;
            $result->fetch = mysqli_fetch_assoc($sql_user);
            $result->rows = mysqli_num_rows($sql_user);
            return $result;
        }else{
          $usercheck = 1;
          return true;
        }
      }
    }else{
      $usercheck = 2;
      return false;
    }
  }else{
    return $usercheck==1;
  }
  }

  public function logout(){
    global $conn;
    if(isset($_COOKIE['token1']) && isset($_COOKIE['token2'])){
      $token1 = mysqli_real_escape_string($conn, $_COOKIE['token1']);
      $token2 = mysqli_real_escape_string($conn, $_COOKIE['token2']);
      $token3 = md5($_SERVER['HTTP_USER_AGENT']);
      $sql_token = mysqli_query($conn, 'DELETE FROM tb_token WHERE token1="'.$token1.'" AND token2="'.$token2.'" AND token3="'.$token3.'"');
      setcookie("token1", "", time() - 3600);
      setcookie("token2", "", time() - 3600);
    }
  }

  public function select($tb = 0,$where = 0){
    global $conn;
    $result = new \stdClass();
    if($tb!=''){
      $query = $where==0 ? 'SELECT * FROM '.$tb : 'SELECT * FROM '.$b.' WHERE '.$where;
      if($sql = mysqli_query($conn, $query)){
        $result->success = true;
        $result->rows = mysqli_num_rows($sql);
        $result->fetch = mysqli_fetch_array($sql);
        return $sql;
      }else{
        $result->success = false;
        return $result;
      }
    }else{
      $result->success = false;
      return $result;
    }
  }

  public function insert($table, $value){
    global $conn;
    if(is_array($value)){
      $query_key = '';
      $query_val = '';
      foreach ($value as $key => $val) {
        $query_key .= $key.',';
        $query_val .= '"'.mysqli_real_escape_string($conn, $val).'",';
      }
      $query = 'INSERT INTO '.$table.' ('.substr($query_key,0,-1).') VALUES ('.substr($query_val,0,-1).')';
      echo $query;
      return mysqli_query($conn, $query);
    }else{
      return false;
    }
  }

  public function update($table,$where,$value){
    global $conn;
    if(is_array($value)){
      $query = 'UPDATE '.$table.' SET ';

      foreach ($value as $key => $val) {
        $query .= $key.'="'.$val.'",';
      }
      $query = substr($query,0,-1) . ' WHERE '.$where;
      echo $query;
      return mysqli_query($conn, $query);
    }else{
      return false;
    }
  }

  public function delete($table,$where){
    global $conn;
    return $sql = mysqli_query($conn, 'DELETE FROM '.$table.' WHERE '.$where);
  }
}
global $mysql;
$mysql = new Mysql();
$class = $mysql;