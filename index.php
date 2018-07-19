<?php
 include 'db_functions.php';
// $_GET['function']
 switch ($_GET['function']) {
 	case 'login':
		$User_email = $_GET['User_email'];
		$User_pass = $_GET['User_pass'];
		showlogin($User_email,$User_pass);
		break;
	case 'getAllTrip':
		$pageTrip = $_GET['pageTrip'];
		$userId = $_GET['userId'];
		showTrip($pageTrip,$userId);
		break;
	case 'getDetailTripInfo':
		$Trip_id = $_GET['Trip_id'];
		showDetailTripInfo($Trip_id);
		break;
	case 'getDetailTripAlone':
		$userId = $_GET['userId'];
		showDetailTripInfoAlone($userId);
		break;
	case 'getDetailTripComment':
		$Trip_id = $_GET['Trip_id'];
		showTDetailTripComment($Trip_id);
		break;
	case 'getUserInfo':
		$userId = $_GET['userId'];
		showUserInfo($userId);
		break;
	case 'getProfile':
		$userId = $_GET['userId'];
		showProfile($userId);
		break;
	case 'getNewFeed':
		showNewFeed();
		break;
	case 'getJoinTrip':
		$Trip_id = $_GET['Trip_id'];
		$userId = $_GET['userId'];
		showJoinTrip($Trip_id, $userId);
		break;
	case 'getFriend':
		$userId = $_GET['userId'];
		showFriend($userId);
		break;
	case 'getLastJoined':
		$userId = $_GET['userId'];
		showLastJoined($userId);
		break;
	default:
		$error = "no method name found.";
		echo json_encode($error);
		exit();
		break;
	}

function showlogin($User_email, $User_pass){ 

	$query = " SELECT * FROM BikeTrip_User WHERE User_email = $User_email AND User_pass = $User_pass";

	$result = db_select($query);
	$array = array();
	$array["status"] = "400";
    $array["data"] = $result;
	$json = json_encode($array);
	print $json;
	exit();
}

function showTrip($pageTrip, $userId){

	$query = " SELECT BikeTrip_Trip.Trip_id, BikeTrip_Trip.Trip_status, BikeTrip_Trip.Trip_name, BikeTrip_Trip.Trip_startName,BikeTrip_Trip.Trip_startLat,BikeTrip_Trip.Trip_startLong, BikeTrip_Trip.Trip_endName, BikeTrip_Trip.Trip_endLat, BikeTrip_Trip.Trip_endLong, BikeTrip_Trip.Trip_detail, BikeTrip_Trip.Trip_type, BikeTrip_Trip.Trip_dateST, BikeTrip_Trip.Trip_leader, BikeTrip_Trip.Trip_image, BikeTrip_User.User_id, BikeTrip_User.User_fullName, BikeTrip_User.User_img FROM BikeTrip_Trip INNER JOIN BikeTrip_User ON BikeTrip_Trip.Trip_leader = BikeTrip_User.User_id";

	// $query = "SELECT BikeTrip_Trip.Trip_id, BikeTrip_Trip.Trip_image FROM BikeTrip_Trip ";

	if ($pageTrip == 1) {
		 $query .= " WHERE BikeTrip_Trip.Trip_status != 3 AND BikeTrip_Trip.Trip_type = 1 ORDER BY BikeTrip_Trip.Trip_id DESC";
	}
	elseif($pageTrip == 2) {
		$query .= " WHERE BikeTrip_Trip.Trip_type = 1 AND BikeTrip_Trip.Trip_type = 1 AND BikeTrip_Trip.Trip_leader = $userId ORDER BY BikeTrip_Trip.Trip_id DESC";
	}
	elseif($pageTrip == 3) {
		$query .= " INNER JOIN BikeTrip_JoinTrip ON BikeTrip_Trip.Trip_id = BikeTrip_JoinTrip.Trip_id WHERE BikeTrip_Trip.Trip_type = 1 AND BikeTrip_JoinTrip.User_id = $userId AND BikeTrip_Trip.Trip_leader != $userId ORDER BY BikeTrip_Trip.Trip_id DESC";
	}

	$result = db_select($query);
	foreach ($result as $key => $value) {
		$value['Trip_id'];
		$queryy = "SELECT DISTINCT user.User_id, user.User_fullName, user.User_gender, user.User_img FROM BikeTrip_User AS user JOIN BikeTrip_JoinTrip AS joined ON user.User_id = joined.User_id WHERE joined.Trip_id = ";
		$queryy .= $value['Trip_id'];
		$userJoined[] = db_select($queryy);
	}
	$array = array();
	$array["status"] = "200";
    $array["data"] = $result;
     
    foreach ($array['data'] as $key => $value) {
    	$array['data'][$key]['User_Joined'] = $userJoined[$key];
    }
	$json = json_encode($array); 
	print $json;
	exit();
}

function showDetailTripInfo($Trip_id){

     $query = " SELECT BikeTrip_Trip.Trip_id, BikeTrip_Trip.Trip_status, BikeTrip_Trip.Trip_name, BikeTrip_Trip.Trip_startName,BikeTrip_Trip.Trip_startLat,BikeTrip_Trip.Trip_startLong, BikeTrip_Trip.Trip_endName, BikeTrip_Trip.Trip_endLat, BikeTrip_Trip.Trip_endLong, BikeTrip_Trip.Trip_detail, BikeTrip_Trip.Trip_type,BikeTrip_Trip.Trip_time, BikeTrip_Trip.Trip_dateST,BikeTrip_Trip.Trip_dateEND,  BikeTrip_Trip.Trip_leader, BikeTrip_Trip.Trip_image, BikeTrip_User.User_id, BikeTrip_User.User_fullName, BikeTrip_User.User_img FROM BikeTrip_Trip INNER JOIN BikeTrip_User ON BikeTrip_Trip.Trip_leader = BikeTrip_User.User_id WHERE BikeTrip_Trip.Trip_id = $Trip_id ";

	$result = db_select($query);
	foreach ($result as $key => $value) {
		$value['Trip_id'];
		$queryy = "SELECT DISTINCT user.User_id, user.User_fullName, user.User_gender, user.User_img FROM BikeTrip_User AS user JOIN BikeTrip_JoinTrip AS joined ON user.User_id = joined.User_id WHERE joined.Trip_id = ";
		$queryy .= $value['Trip_id'];
		$userJoined[] = db_select($queryy);
	}
	$array = array();
	$array["status"] = "400";
    $array["data"] = $result;
      foreach ($array['data'] as $key => $value) {
    	$array['data'][$key]['User_Joined'] = $userJoined[$key];
    }
	$json = json_encode($array);
	print $json;
	exit();
}

function showDetailTripInfoAlone($userId){

     $query = " SELECT * FROM BikeTrip_Trip WHERE BikeTrip_Trip.Trip_leader = $userId AND (SELECT MAX(BikeTrip_Trip.Trip_id) FROM BikeTrip_Trip WHERE BikeTrip_Trip.Trip_leader = $userId)  = BikeTrip_Trip.Trip_id ";

	$result = db_select($query);
	$array = array();
	$array["status"] = "400";
    $array["data"] = $result;
	$json = json_encode($array);
	print $json;
	exit();
}


function showTDetailTripComment($Trip_id) {

	$query = "SELECT BikeTrip_CommTrip.Trip_id, BikeTrip_CommTrip.User_id, BikeTrip_User.User_fullName, BikeTrip_User.User_img, BikeTrip_CommTrip.CommTrip_date, BikeTrip_CommTrip.CommTrip_detail FROM BikeTrip_CommTrip INNER JOIN BikeTrip_User ON BikeTrip_CommTrip.User_id = BikeTrip_User.User_id WHERE Trip_id = $Trip_id ORDER BY BikeTrip_CommTrip.CommTrip_date DESC";

	$result = db_select($query);
	$array = array();
	$array["status"] = "400";
    $array["data"] = $result;
	$json = json_encode($array);
	print $json;
	exit();
}

function showUserInfo($userId) {

	$query = "SELECT * FROM BikeTrip_User WHERE User_id = $userId";

	$result = db_select($query);
	$array = array();
	$array["status"] = "400";
    $array["data"] = $result;
	$json = json_encode($array);
	print $json;
	exit();
}

function showProfile($userId) {

	// $query = "SELECT * FROM BikeTrip_User WHERE User_id = $userId";
	// $resultUser = db_select($query);

	$query = "SELECT IFNULL(SUM(JoinTrip_CyclingDtn),0) AS Sum_cyclingDt, COUNT(BikeTrip_JoinTrip.Trip_id) AS Count_tripJoin FROM BikeTrip_JoinTrip INNER JOIN BikeTrip_Trip ON BikeTrip_JoinTrip.Trip_id = BikeTrip_Trip.Trip_id WHERE BikeTrip_JoinTrip.User_id = $userId AND BikeTrip_Trip.Trip_status = 3";
	$cycDtAndTripJoin = db_select($query);

	$queryy = "SELECT COUNT(Trip_id) AS Count_tripLeader FROM BikeTrip_Trip WHERE Trip_leader = $userId AND Trip_status = 3 ";
	$tripLeader[] = db_select($queryy);

	$queryyy = "SELECT BikeTrip_JoinTrip.Trip_id, BikeTrip_Trip.Trip_name, BikeTrip_Trip.Trip_leader, BikeTrip_User.User_fullName, BikeTrip_JoinTrip.JoinTrip_CyclingDtn, BikeTrip_JoinTrip.JoinTrip_CyclingTime, BikeTrip_JoinTrip.JoinTrip_CyclingKcal, BikeTrip_JoinTrip.JoinTrip_ResMap FROM BikeTrip_JoinTrip INNER JOIN BikeTrip_Trip ON BikeTrip_JoinTrip.Trip_id = BikeTrip_Trip.Trip_id INNER JOIN BikeTrip_User ON BikeTrip_Trip.Trip_leader = BikeTrip_User.User_id WHERE BikeTrip_JoinTrip.User_id = $userId AND BikeTrip_Trip.Trip_status = 3";
	$tripRes[] = db_select($queryyy);
	
	$array = array();
	$array["status"] = "200";
    $array["data"] = $cycDtAndTripJoin;
     
    foreach ($array['data'] as $key => $value) {
    	$array['data'][$key]['CountTripLeader'] = $tripLeader[$key];
    	$array['data'][$key]['TripRes'] = $tripRes[$key];
    }
	$json = json_encode($array); 
	print $json;
	exit();
}

function showJoinTrip($Trip_id, $userId) {

	$query = "SELECT JoinTrip_id FROM BikeTrip_JoinTrip WHERE Trip_id = $Trip_id AND User_id = $userId";

	$result = db_select($query);
	$array = array();
	$array["status"] = "400";
    $array["data"] = $result;
	$json = json_encode($array);
	print $json;
	exit();
}

function showNewFeed() {

	$query = "SELECT BikeTrip_Social.Social_id,BikeTrip_Social.Social_date,BikeTrip_Social.Social_detail, BikeTrip_JoinTrip.JoinTrip_id, BikeTrip_Social.User_id, BikeTrip_User.User_fullName, BikeTrip_User.User_img, BikeTrip_JoinTrip.Trip_id, BikeTrip_Trip.Trip_leader, (SELECT BikeTrip_User.User_fullName FROM BikeTrip_User INNER JOIN BikeTrip_Trip ON BikeTrip_User.User_id = BikeTrip_Trip.Trip_leader WHERE BikeTrip_Trip.Trip_id = BikeTrip_JoinTrip.Trip_id) AS User_LeaderName, BikeTrip_Trip.Trip_name, BikeTrip_JoinTrip.JoinTrip_CyclingDtn, BikeTrip_JoinTrip.JoinTrip_CyclingTime, BikeTrip_JoinTrip.JoinTrip_CyclingKcal, BikeTrip_JoinTrip.JoinTrip_ResMap FROM BikeTrip_Social INNER JOIN BikeTrip_JoinTrip ON BikeTrip_Social.JoinTrip_id = BikeTrip_JoinTrip.JoinTrip_id INNER JOIN BikeTrip_User ON BikeTrip_Social.User_id = BikeTrip_User.User_id INNER JOIN BikeTrip_Trip ON BikeTrip_JoinTrip.Trip_id = BikeTrip_Trip.Trip_id ORDER BY BikeTrip_Social.Social_date DESC";

	$result = db_select($query);
	foreach ($result as $key => $value) {
		$value['JoinTrip_id'];
		$queryy = "SELECT DISTINCT BikeTrip_JoinImage.JoinImage_path FROM BikeTrip_JoinImage INNER JOIN BikeTrip_JoinTrip ON BikeTrip_JoinImage.JoinTrip_id = BikeTrip_JoinTrip.JoinTrip_id WHERE BikeTrip_JoinImage.JoinTrip_id = ";
		$queryy .= $value['JoinTrip_id'];
		$userJoined[] = db_select($queryy);
	}
	$array = array();
	$array["status"] = "200";
    $array["data"] = $result;
     
    foreach ($array['data'] as $key => $value) {
    	$array['data'][$key]['Photo_Trip'] = $userJoined[$key];
    }
	$json = json_encode($array); 
	print $json;
	exit();
}

function showFriend($userId) {

	$query = "SELECT BikeTrip_Relationship.user_one_id, BikeTrip_Relationship.user_two_id, BikeTrip_Relationship.Relationship_status, BikeTrip_User.User_fullName, BikeTrip_User.User_img FROM BikeTrip_Relationship INNER JOIN BikeTrip_User ON BikeTrip_Relationship.user_two_id = BikeTrip_User.User_id WHERE user_one_id = $userId AND BikeTrip_Relationship.Relationship_status = 2";

	$result = db_select($query);
	$array = array();
	$array["status"] = "400";
    $array["data"] = $result;
	$json = json_encode($array);
	print $json;
	exit();
}

function showLastJoined($userId) {

	$query = "SELECT BikeTrip_JoinTrip.Trip_id FROM BikeTrip_JoinTrip INNER JOIN BikeTrip_User ON BikeTrip_JoinTrip.User_id = BikeTrip_User.User_id WHERE BikeTrip_JoinTrip.User_id = $userId ORDER BY JoinTrip_dateTime DESC LIMIT 1";

	$result = db_select($query);
	
	foreach ($result as $key => $value) {
		$value['Trip_id'];
		$queryy = "SELECT BikeTrip_JoinTrip.User_id, BikeTrip_User.User_fullName, BikeTrip_User.User_img FROM BikeTrip_JoinTrip INNER JOIN BikeTrip_User ON BikeTrip_JoinTrip.User_id = BikeTrip_User.User_id WHERE BikeTrip_JoinTrip.Trip_id = " ;
		$queryy .= $value['Trip_id']. " " . "AND BikeTrip_JoinTrip.User_id != $userId" ;
		
		$userJoined[] = db_select($queryy);
	}

	$array = array();
	$array["status"] = "200";
    $array["data"] = $result;
     
    foreach ($array['data'] as $key => $value) {
    	$array['data'][$key]['User_Joined'] = $userJoined[$key];
    }
	$json = json_encode($array); 
	print $json;
	exit();
}


?>