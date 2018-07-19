<?php
function db_connect() {
    static $connection;
    if(!isset($connection)) {
		$config = parse_ini_file('config.ini');
        // echo $config['username'];
        // echo $config['password'];
        // echo $config['dbname'];
        $connection = mysqli_connect('localhost',$config['username'],$config['password'],$config['dbname']);
        mysqli_query($connection,"SET NAMES UTF8");
    }

    if($connection === false) {
        return mysqli_connect_error();
    }
    return $connection;
}

function debugPrint() {
    $test = "test";
    echo json_encode($test);
    exit();
}

function db_query($query) {
    $connection = db_connect();
    $result = mysqli_query($connection,$query);
    mysqli_query($connection,"SET character_set_results=utf8");
  	mysqli_query($connection,"SET character_set_client=utf8");
  	mysqli_query($connection,"SET character_set_connection=utf8");
    if (!$result) {
        $array = array();
        $array["status"] = "404";
        $array["message"] = mysqli_error($connection);
        echo json_encode($array);
        exit();
    }
    return $result;

}

function db_select($query) {
    // print $query."<br/>";
    $rows = array();
    $result = db_query($query);

    if($result === false) {
        $array = array();
        $array["status"] = "404";
        $array["message"] = "select error.";
        echo json_encode($array);
        exit();
    }

    while($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function db_insert($query) {
    // print $query."<br/>";

    $result = db_query($query);

    if($result === false) {
        $array = array();
        $array["status"] = "404";
        $array["message"] = "insert error.";
        echo json_encode($array);
        exit();
    }
    //row id
    return $result;
}


function db_delete($query) {
  $result = db_query($query);

  if($result === false) {
      $array = array();
      $array["status"] = "404";
      $array["message"] = "delete error.";
      echo json_encode($array);
      exit();
  }
}

function db_error() {
    $connection = db_connect();
    return mysqli_error($connection);
}

function db_quote($value) {
    $connection = db_connect();
    return "'" . mysqli_real_escape_string($connection,$value) . "'";
}
