<?php
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		session_start();
		if (empty($_POST['phonenumber']) || empty($_POST['password'])) {
			$code = "1002";
			$message = 'Parameter is not enought.';
		} else {
			require('Config.php');
			$conn= mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASENAME);
			if ($conn == null) {
				$code = '1001';
				$message = 'Can not connect to DB.';
			} else {
				$data = array();
				require('utils.php');

				$phonenumber = $_POST['phonenumber'];
				$password = $_POST['password'];
				$sql = "SELECT * FROM user WHERE phonenumber = $phonenumber AND password = $password";
				$result = mysqli_query($conn, $sql);
				if (mysqli_num_rows($result) == 0) {
					$code = "9995";
					$message = "User is not validated";
				} else {
					$row = mysqli_fetch_assoc($result);
					$data["id"] = $row["id"];
					$data["username"] = $row["username"];
					$data["avatar"] = $row["avatar"];
					$token = $row["id"]."_".$row["username"]."_".getTimeInString();
					$token = base64_encode($token);
					$data["token"] = $token;
					$sql = "UPDATE user SET token = '$token' WHERE phonenumber = $phonenumber";
					mysqli_query($conn, $sql);
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