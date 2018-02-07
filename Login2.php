<?php
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		session_start();
		if (!isset($_POST['token']) ) {
			$code = "1002";
			$message = 'Parameter is not enought.';
		} else {
			require('Config.php');
			$conn= mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASENAME);
			if ($conn == null) {
				$code = '1001';
				$message = 'Can not connect to DB.';
			} else {
				require('utils.php');
				$token = $_POST['token'];
				$user_id = getUserByToken($token);
				$sql = "SELECT * FROM user WHERE token = '$token'";
				$result = mysqli_query($conn, $sql);
				if (mysqli_num_rows($result) == 0) {
					$code = '9998';
					$message = 'Token is invalid';
				} else {
					$data = array();
					$sql_receiver = "SELECT * FROM user WHERE id = '$user_id'";
					$result_receiver = mysqli_query($conn, $sql_receiver);
					$row_receiver = mysqli_fetch_assoc($result_receiver);
					$data["id"] = $row_receiver["id"];
					$data["username"] = $row_receiver["username"];
					$data["avatar"] = $row_receiver["avatar"];
					$token = $row_receiver["id"]."_".$row_receiver["username"]."_".getTimeInString();
					$token = base64_encode($token);
					$data["token"] = $token;
					$sql_update = "UPDATE user SET token = '$token' WHERE id = '$user_id'";
					mysqli_query($conn, $sql_update);
					$_SESSION['id'] = $token;
					$code = "1000";
					$message = "OK";
				}
			}
		}
	} else {
		$code = '9997';
		$message = 'Method is invalid';
	}

	$array = array(
		'code' => $code,
		'message' => $message);
	if (isset($data)) {
		$array['data'] = $data;
	}
	$ouput = json_encode($array);
	echo $ouput;
?>