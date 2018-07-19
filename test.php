<?php


date_default_timezone_set('Asia/Bangkok');

require 'vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

define('CLIENT_URL', '*');
define('UPLOAD_IMAGE_PATH', 'upload/image');
define('API_ACCESS_KEY', 'AAAAY5fdejI:APA91bHH_h1qw3nZhP4vB5cv7htuRCN0ByUXlY2FnDMD55Vrvm8vlBK-zXhG9Viuxu3f_Of3LnLHNPkyg7zIbrD7q_2EgzjIdGpAmA4vJtCkswfFmtdShZwVwjmrKvYSZ_vLpLc44pAA');

$app = new \Slim\App(['settings' => ['displayErrorDetails' => true]]);
// unset($app->getContainer()['errorHandler']);

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', CLIENT_URL)
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

function getDatabase() {
    $servername = "localhost";
    $username = "u13570176";
    $password = "nt5Q728e8C";
    $dbname = "db13570176";
    // $username = "root";
    // $password = "";
    // $dbname = "BikeTrip";

    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $conn->exec("set names utf8");

    return $conn;
}

function sendNoti($title, $body, $target) {
  $msg = array(
        'title' => $title,
        'body'  => $body
    );

    $fields = array(
        'registration_ids' => $target,
        'notification' => $msg
    );


    $headers = array(
        'Authorization: key=' . API_ACCESS_KEY,
        'Content-Type: application/json'
    );

    $ch = curl_init();
    curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
    curl_setopt( $ch,CURLOPT_POST, true );
    curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
    curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
    $result = curl_exec($ch );
    curl_close( $ch );
}

$app->post('/register', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    $User_email = $data["User_email"];
    $User_pass = $data["User_pass"];
    $User_fullName = $data["User_fullName"];
    $User_gender = $data["User_gender"];
    $User_wate = $data["User_wate"];
    $User_height = $data["User_height"];

    $files = $request->getUploadedFiles();
    if (!empty($files['imageFile'])) {
        $imageFile = $files['imageFile'];
        if ($imageFile->getError() === UPLOAD_ERR_OK) {
            $uploadFileName = uniqid();
            $imageFile->moveTo(UPLOAD_IMAGE_PATH . "/$uploadFileName" . ".jpg");

            $User_img = UPLOAD_IMAGE_PATH . "/$uploadFileName" . ".jpg";

            try {
                $db = getDatabase();

                $stmt = $db->prepare("INSERT INTO BikeTrip_User (User_email, User_pass, User_fullName, User_gender, User_wate, User_height, User_img) VALUES (:User_email, :User_pass, :User_fullName, :User_gender, :User_wate, :User_height, :User_img)");

                $stmt->bindParam(':User_email', $User_email);
                $stmt->bindParam(':User_pass', $User_pass);
                $stmt->bindParam(':User_fullName', $User_fullName);
                $stmt->bindParam(':User_gender', $User_gender);
                $stmt->bindParam(':User_wate', $User_wate);
                $stmt->bindParam(':User_height', $User_height);
                $stmt->bindParam(':User_img', $User_img);
                $stmt->execute();
         
                return $response->withJson(array('message' => 'success'));
            } catch(PDOException $e) {
                return $response->withJson(array('message' => 'fail', 'error' => $e->getMessage()));
            }
        } else {
            return $response->withJson(array('message' => 'fail', 'error' => 'รูปเสียหาย'));
        }
    } else {
        return $response->withJson(array('message' => 'fail', 'error' => 'ไม่มีรูปภาพ'));
    }
});

$app->post('/login', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    $User_email = $data["User_email"];
    $User_pass = $data["User_pass"];

    try {
        $db = getDatabase();

        $stmt = $db->prepare("SELECT * FROM BikeTrip_User WHERE User_email = :User_email AND User_pass = :User_pass");

        $stmt->bindParam(':User_email', $User_email);
        $stmt->bindParam(':User_pass', $User_pass);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $response->withJson($result);
    } catch(PDOException $e) {
        echo $e->getMessage();
    }
});

$app->post('/infos', function (Request $request, Response $response) {
    // $menu_type_id = $request->getAttribute('menu_type_id');

    try {
        $db = getDatabase();

        $stmt = $db->prepare("SELECT * FROM info INNER JOIN user ON info.info_owner = user.user_id");

        // $stmt->bindParam(':menu_type_id', $menu_type_id);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $response->withJson($result);
    } catch(PDOException $e) {
        echo $e->getMessage();
    }
});

$app->post('/joinTrip', function (Request $request, Response $response) {
    // $menu_type_id = $request->getAttribute('menu_type_id');

    $data = $request->getParsedBody();
       
    $Trip_id = $data["Trip_id"];
    $User_id = $data["User_id"];
    // $Type_joinTrip = $data["Type_joinTrip"];

    try {
      $db = getDatabase();

            $stmt = $db->prepare("INSERT INTO BikeTrip_JoinTrip (Trip_id, User_id) VALUES ((SELECT BikeTrip_Trip.Trip_id FROM BikeTrip_Trip WHERE BikeTrip_Trip.Trip_id = :Trip_id ), (SELECT BikeTrip_User.User_id FROM BikeTrip_User WHERE BikeTrip_User.User_id = :User_id))");
            $stmt->bindParam(':Trip_id', $Trip_id);
            $stmt->bindParam(':User_id', $User_id);

        $stmt->execute();

        return $response->withJson(array('message' => 'success'));
        
        } catch(PDOException $e) {
            return $response->withJson(array('message' => 'fail', 'error' => $e->getMessage()));   
        } 
});

$app->post('/canceledTrip', function (Request $request, Response $response) {
    // $menu_type_id = $request->getAttribute('menu_type_id');

    $data = $request->getParsedBody();
       
    $Trip_id = $data["Trip_id"];
    $User_id = $data["User_id"];

    try {
      $db = getDatabase();

          $stmt = $db->prepare("DELETE FROM BikeTrip_JoinTrip WHERE User_id = :User_id AND Trip_id = :Trip_id");

          $stmt->bindParam(':Trip_id', $Trip_id);
          $stmt->bindParam(':User_id', $User_id);

        $stmt->execute();

        return $response->withJson(array('message' => 'success'));
        
        } catch(PDOException $e) {
            return $response->withJson(array('message' => 'fail', 'error' => $e->getMessage()));   
        } 
});

$app->post('/addData', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
       
    $Trip_leader = $data["Trip_leader"];
    $Trip_status = $data["Trip_status"];
    $Trip_name = $data["Trip_name"];
    // $Trip_type = $data["Trip_type"];
    $Trip_startName = $data["Trip_startName"];
    $Trip_startLat = $data["Trip_startLat"];
    $Trip_startLong = $data["Trip_startLong"];
    $Trip_endName = $data["Trip_endName"];
    $Trip_endLat = $data["Trip_endLat"];
    $Trip_endLong = $data["Trip_endLong"];
    $Trip_time = $data["Trip_time"];
    $Trip_dateST = $data["Trip_dateST"];
    $Trip_dateEND = $data["Trip_dateEND"];
    $Trip_detail = $data["Trip_detail"];
    // $Invites = $data["Invites"];

    $files = $request->getUploadedFiles();
    if (!empty($files['imageFile'])) {
        $imageFile = $files['imageFile'];
        if ($imageFile->getError() === UPLOAD_ERR_OK) {
            $uploadFileName = uniqid();
            $imageFile->moveTo(UPLOAD_IMAGE_PATH . "/$uploadFileName" . ".jpg");

            $Trip_image = UPLOAD_IMAGE_PATH . "/$uploadFileName" . ".jpg";
        } else {
            return $response->withJson(array('message' => 'fail', 'error' => 'รูปเสียหาย'));
        }
    } else {
        $Trip_image = "upload/image/tripBgDefault.jpg";
        // return $response->withJson(array('message' => 'fail', 'error' => 'ไม่มีรูปภาพ ใช่รูป Default'));
    }

    try {
      $db = getDatabase();

          $stmt = $db->prepare("INSERT INTO BikeTrip_Trip (Trip_leader, Trip_status, Trip_name, Trip_startName, Trip_startLat, Trip_startLong, Trip_endName, Trip_endLat, Trip_endLong, Trip_time, Trip_dateST, Trip_dateEND, Trip_detail, Trip_image) VALUES ((SELECT BikeTrip_User.User_id FROM BikeTrip_User WHERE BikeTrip_User.User_id = :Trip_leader ), :Trip_status, :Trip_name, :Trip_startName, :Trip_startLat, :Trip_startLong, :Trip_endName, :Trip_endLat, :Trip_endLong, :Trip_time, :Trip_dateST, :Trip_dateEND, :Trip_detail, :Trip_image)");

          $stmt->bindParam(':Trip_leader', $Trip_leader);
          $stmt->bindParam(':Trip_status', $Trip_status);
          $stmt->bindParam(':Trip_name', $Trip_name);          
          // $stmt->bindParam(':Trip_type', $Trip_type);
          $stmt->bindParam(':Trip_startName', $Trip_startName);
          $stmt->bindParam(':Trip_startLat', $Trip_startLat);
          $stmt->bindParam(':Trip_startLong', $Trip_startLong);
          $stmt->bindParam(':Trip_endName', $Trip_endName);
          $stmt->bindParam(':Trip_endLat', $Trip_endLat);
          $stmt->bindParam(':Trip_endLong', $Trip_endLong);
          $stmt->bindParam(':Trip_time', $Trip_time);
          $stmt->bindParam(':Trip_dateST', $Trip_dateST);
          $stmt->bindParam(':Trip_dateEND', $Trip_dateEND);
          $stmt->bindParam(':Trip_detail', $Trip_detail);
          $stmt->bindParam(':Trip_image', $Trip_image);
          $stmt->execute();

          // if ($Invites != "") {
          //   //ส่ง noti ถึง?
          //   for ($i = 0; $i < count($Invites); $i++) {
          //     $stmt_owner = $db->prepare("SELECT * FROM BikeTrip_User WHERE User_id = :Invites");
          //     $stmt_owner->bindParam(':Invites', $Invites[$i]);
          //     $stmt_owner->execute();
          //     $result_owner += $stmt_owner->fetchAll(PDO::FETCH_ASSOC);
          //   }

          //   $arrayNoti = array();
          //   foreach ($result_owner as $key => $value) {
          //     array_push($arrayNoti, $value["user_noti_token"]);
          //   }

          //   //คนส่ง noti
          //   $stmt_join = $db->prepare("SELECT * FROM BikeTrip_User WHERE User_id = :Trip_leader");

          //   $stmt->bindParam(':Trip_leader', $Trip_leader);
          //   $stmt_join->execute();
          //   $result_join = $stmt_join->fetchAll(PDO::FETCH_ASSOC);

          //   $joinName = $result_join[0]["User_fullName"]; 
          //   $body = $joinName . " ชวนคุณเข้าร่วมทริป : " . $Trip_name ;
          //   $bodyHtml = $body ;
          //   sendNoti("BikeTrip", $body, $arrayNoti);

          //   for ($i = 0; $i < count($result_owner); $i++) {
          //     $stmt_noti = $db->prepare("INSERT INTO BikeTrip_Notification (notification_detail, notification_datetime, notification_userImage, notification_userId) VALUES (:notification_detail, NOW(), :notification_userImage, :notification_userId)");

          //     $stmt_noti->bindParam(':notification_detail', $bodyHtml);
          //     $stmt_noti->bindParam(':notification_userImage', $result_join[0]["User_img"]);
          //     $stmt_noti->bindParam(':notification_userId', $result_owner[$i]["User_id"]);
          //     $stmt_noti->execute();
          //   }
          // }

          return $response->withJson(array('message' => 'success'));
      } catch(PDOException $e) {
          return $response->withJson(array('message' => 'fail', 'error' => $e->getMessage()));
      }
    } else {
            return $response->withJson(array('message' => 'fail', 'error' => 'รูปเสียหาย'));
        }
    } else {
        return $response->withJson(array('message' => 'fail', 'error' => 'ไม่มีรูปภาพ'));
    }
});

$app->post('/commentTrip', function (Request $request, Response $response) {
 
    $data = $request->getParsedBody();
       
    $Trip_id = $data["Trip_id"];
    $User_id = $data["User_id"];
    $CommTrip_detail = $data["CommTrip_detail"];

    try {
      $db = getDatabase();

          $stmt = $db->prepare("INSERT INTO BikeTrip_CommTrip( Trip_id , User_id , CommTrip_detail) VALUES ((SELECT BikeTrip_Trip.Trip_id FROM BikeTrip_Trip WHERE BikeTrip_Trip.Trip_id = "." :Trip_id "."), (SELECT BikeTrip_User.User_id FROM BikeTrip_User WHERE BikeTrip_User.User_id = "." :User_id ".") , :CommTrip_detail)");

          $stmt->bindParam(':Trip_id', $Trip_id);
          $stmt->bindParam(':User_id', $User_id);
          $stmt->bindParam(':CommTrip_detail', $CommTrip_detail);
          $stmt->execute();

          //ส่ง noti ถึง?
          $stmt_owner = $db->prepare("SELECT * FROM BikeTrip_Trip INNER JOIN BikeTrip_User ON BikeTrip_Trip.Trip_leader = BikeTrip_User.User_id WHERE BikeTrip_Trip.Trip_id = :Trip_id");

          $stmt_owner->bindParam(':Trip_id', $Trip_id);
          $stmt_owner->execute();
          $result_owner = $stmt_owner->fetchAll(PDO::FETCH_ASSOC);

          $arrayNoti = array();
          foreach ($result_owner as $key => $value) {
            array_push($arrayNoti, $value["user_noti_token"]);
          }

          //คนส่ง noti
          $stmt_join = $db->prepare("SELECT * FROM BikeTrip_CommTrip INNER JOIN BikeTrip_User ON BikeTrip_CommTrip.User_id = BikeTrip_User.User_id WHERE BikeTrip_CommTrip.User_id = :User_id");

          $stmt_join->bindParam(':Trip_id', $Trip_id);
          $stmt_join->bindParam(':User_id', $User_id);
          $stmt_join->execute();
          $result_join = $stmt_join->fetchAll(PDO::FETCH_ASSOC);

          $joinName = $result_join[0]["User_fullName"]; 
          $infoName = $result_owner[0]["User_fullName"];
          $tripName = $result_join[0]["Trip_name"];
          $body = $joinName . " ได้แสดงความคิดเห็นในทริป " . $tripName . "ของคุณ";
          $bodyHtml = $body ;
          sendNoti("BikeTrip", $body, $arrayNoti);

          $stmt_noti = $db->prepare("INSERT INTO BikeTrip_Notification (notification_detail, notification_datetime, notification_userImage, notification_userId) VALUES (:notification_detail, NOW(), :notification_userImage, :notification_userId)");

          $stmt_noti->bindParam(':notification_detail', $bodyHtml);
          $stmt_noti->bindParam(':notification_userImage', $result_join[0]["User_img"]);
          $stmt_noti->bindParam(':notification_userId', $result_owner[0]["User_id"]);
          $stmt_noti->execute();

          return $response->withJson(array('message' => 'success'));
        
        } catch(PDOException $e) {
            return $response->withJson(array('message' => 'fail', 'error' => $e->getMessage()));   
        } 
});

$app->POST('/{User_id}/updateStatusTrip', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    
    $User_id = $request->getAttribute('User_id');   
    $Trip_id = $data["Trip_id"];
    $Trip_status = $data["Trip_status"];

    try {
      $db = getDatabase();

          $stmt = $db->prepare("UPDATE BikeTrip_Trip SET Trip_status = :Trip_status WHERE Trip_id = :Trip_id");

          $stmt->bindParam(':Trip_id', $Trip_id);
          $stmt->bindParam(':Trip_status', $Trip_status);
          $stmt->execute();

          if ($Trip_status == "2") {
              //คส่ง noti ถึง?
              $stmt_owner = $db->prepare("SELECT * FROM BikeTrip_JoinTrip INNER JOIN BikeTrip_User ON BikeTrip_JoinTrip.User_id = BikeTrip_User.User_id WHERE Trip_id = :Trip_id");

              $stmt_owner->bindParam(':Trip_id', $Trip_id);
              $stmt_owner->execute();
              $result_owner = $stmt_owner->fetchAll(PDO::FETCH_ASSOC);

              $arrayNoti = array();
              foreach ($result_owner as $key => $value) {
                array_push($arrayNoti, $value["user_noti_token"]);
              }

              //คนส่ง noti
              $stmt_join = $db->prepare("SELECT * FROM BikeTrip_User INNER JOIN BikeTrip_Trip ON BikeTrip_User.User_id = BikeTrip_Trip.Trip_leader WHERE User_id = :User_id");

              $stmt_join->bindParam(':User_id', $User_id);
              $stmt_join->execute();

              $result_join = $stmt_join->fetchAll(PDO::FETCH_ASSOC);

              $joinName = $result_join[0]["User_fullName"]; 
              $infoName = $result_owner[0]["User_fullName"];
              $tripName = $result_join[0]["Trip_name"];
              $body = " ทริป " . $tripName . " ที่คุณเข้าร่วมเริ่มปั่นแล้ว";
              $bodyHtml = $body  ;
              sendNoti("BikeTrip", $body, $arrayNoti);

              for ($i = 0; $i < count($result_owner); $i++) {
                $stmt_noti = $db->prepare("INSERT INTO BikeTrip_Notification (notification_detail, notification_datetime, notification_userImage, notification_userId) VALUES (:notification_detail, NOW(), :notification_userImage, :notification_userId)");
              }

              $stmt_noti->bindParam(':notification_detail', $bodyHtml);
              $stmt_noti->bindParam(':notification_userImage', $result_join[0]["User_img"]);
              $stmt_noti->bindParam(':notification_userId', $result_owner[0]["User_id"]);
              $stmt_noti->execute();
          }

          return $response->withJson(array('message' => 'success'));
        
        } catch(PDOException $e) {
            return $response->withJson(array('message' => 'fail', 'error' => $e->getMessage()));   
        }  
});

$app->POST('/update', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
       
    $User_id = $data["User_id"];
    $User_fullName = $data["User_fullName"];
    $User_wate = $data["User_wate"];
    $User_height = $data["User_height"];

    try {
      $db = getDatabase();

          $stmt = $db->prepare("UPDATE BikeTrip_User SET User_fullName = :User_fullName, User_wate = :User_wate, User_height = :User_height WHERE User_id = :User_id");

          $stmt->bindParam(':User_id', $User_id);
          $stmt->bindParam(':User_fullName', $User_fullName);
          $stmt->bindParam(':User_wate', $User_wate);
          $stmt->bindParam(':User_height', $User_height);

        $stmt->execute();

        return $response->withJson(array('message' => 'success'));
        
        } catch(PDOException $e) {
            return $response->withJson(array('message' => 'fail', 'error' => $e->getMessage()));   
        } 
});

$app->POST('/resultsCycling', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
       
    $Trip_id = $data["Trip_id"];
    $User_id = $data["User_id"];
    $JoinTrip_CyclingDtn = $data["JoinTrip_CyclingDtn"];
    $JoinTrip_CyclingTime = $data["JoinTrip_CyclingTime"];
    $JoinTrip_CyclingKcal = $data["JoinTrip_CyclingKcal"];

    $files = $request->getUploadedFiles();
    $imageFiles = array();
    for ($i = 1; $i <= count($files); $i++) {
      if (!empty($files['imageFile' . $i])) {
        $imageFile = $files['imageFile' . $i];

        if ($imageFile->getError() === UPLOAD_ERR_OK) {
          $uploadFileName = uniqid();
              $imageFile->moveTo(UPLOAD_IMAGE_PATH . "/$uploadFileName" . ".jpg");

              $imageFileName = UPLOAD_IMAGE_PATH . "/$uploadFileName" . ".jpg";
              array_push($imageFiles, $imageFileName);
        }
      }
    }

    // $files = $request->getUploadedFiles();
    // if (!empty($files['imageFile'])) {
    //     $imageFile = $files['imageFile'];
    //     if ($imageFile->getError() === UPLOAD_ERR_OK) {
    //       $uploadFileName = uniqid();
    //         $imageFile->moveTo(UPLOAD_IMAGE_PATH . "/$uploadFileName" . ".jpg");

    //         $JoinTrip_ResMap = UPLOAD_IMAGE_PATH . "/$uploadFileName" . ".jpg";

    try {
      $db = getDatabase();

        $stmt = $db->prepare("UPDATE BikeTrip_JoinTrip SET JoinTrip_CyclingDtn  = :JoinTrip_CyclingDtn, JoinTrip_CyclingTime = :JoinTrip_CyclingTime, JoinTrip_CyclingKcal = :JoinTrip_CyclingKcal  WHERE Trip_id = :Trip_id AND User_id = :User_id");

        $stmt->bindParam(':Trip_id', $Trip_id);
        $stmt->bindParam(':User_id', $User_id);
        $stmt->bindParam(':JoinTrip_CyclingDtn', $JoinTrip_CyclingDtn);
        $stmt->bindParam(':JoinTrip_CyclingTime', $JoinTrip_CyclingTime);
        $stmt->bindParam(':JoinTrip_CyclingKcal', $JoinTrip_CyclingKcal);
        // $stmt->bindParam(':JoinTrip_ResMap', $JoinTrip_ResMap);
        $stmt->execute();

        $info_id = $db->lastInsertId();
        for ($i = 0; $i < count($imageFiles); $i++) {
          
          $stmt = $db->prepare("INSERT INTO BikeTrip_JoinImage ( JoinTrip_id , JoinImage_path ) VALUES ((SELECT BikeTrip_JoinTrip.JoinTrip_id FROM BikeTrip_JoinTrip WHERE Trip_id = :Trip_id AND User_id = :User_id ), :JoinImage_path )");
          $stmt->bindParam(':Trip_id', $Trip_id);
          $stmt->bindParam(':User_id', $User_id);
          $stmt->bindParam(':JoinImage_path', $imageFiles[$i]);
          $stmt->execute();
        }

        return $response->withJson(array('message' => 'success'));
        
        } catch(PDOException $e) {
            return $response->withJson(array('message' => 'fail', 'error' => $e->getMessage()));   
        }
    } else {
            return $response->withJson(array('message' => 'fail', 'error' => 'รูปเสียหาย'));
        }
    } else {
        return $response->withJson(array('message' => 'fail', 'error' => 'ไม่มีรูปภาพ'));
    } 
});

$app->POST('/share', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
       
    $Trip_id = $data["Trip_id"];
    $User_id = $data["User_id"];
    $Social_detail = $data["Social_detail"];

    try {
      $db = getDatabase();

          $stmt = $db->prepare("INSERT INTO BikeTrip_Social ( User_id, joinTrip_id,  Social_detail) VALUES ((SELECT BikeTrip_User.User_id FROM BikeTrip_User WHERE BikeTrip_User.User_id = :User_id), (SELECT BikeTrip_JoinTrip.JoinTrip_id FROM BikeTrip_JoinTrip WHERE BikeTrip_JoinTrip.User_id = :User_id AND BikeTrip_JoinTrip.Trip_id = :Trip_id), :Social_detail)");

          $stmt->bindParam(':Trip_id', $Trip_id);
          $stmt->bindParam(':User_id', $User_id);
          $stmt->bindParam(':Social_detail', $Social_detail);

        $stmt->execute();

        return $response->withJson(array('message' => 'success'));
        
        } catch(PDOException $e) {
            return $response->withJson(array('message' => 'fail', 'error' => $e->getMessage()));   
        } 
});

$app->post('/tokenUpdate', function (Request $request, Response $response) {
  // $User_id = $request->getAttribute('User_id');

    $data = $request->getParsedBody();
    $User_id = $data["User_id"];
    $User_noti_token = $data["User_noti_token"];

    try {
    $db = getDatabase();

    $stmt = $db->prepare("UPDATE BikeTrip_User SET User_noti_token = :User_noti_token WHERE User_id = :User_id");

    
    $stmt->bindParam(':User_id', $User_id);
    $stmt->bindParam(':User_noti_token', $User_noti_token);
      $stmt->execute();

      return $response->withJson(array('message' => 'success'));
  } catch(PDOException $e) {
    return $response->withJson(array('message' => 'fail', 'error' => $e->getMessage()));
  }
});

$app->post('/user/{User_id}/notification', function (Request $request, Response $response) {
    $User_id = $request->getAttribute('User_id');

    try {
        $db = getDatabase();

        $stmt = $db->prepare("SELECT * FROM BikeTrip_Notification WHERE notification_userId = :User_id ORDER BY notification_datetime DESC");

        $stmt->bindParam(':User_id', $User_id);
        $stmt->execute();

         $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $response->withJson($result);
    } catch(PDOException $e) {
        echo $e->getMessage();
    }
});

$app->run();

?>
