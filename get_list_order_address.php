<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!isset($_POST['token'])) {
		$code = "1002";
		$message = 'Parameter is not enought.';
	} else {
		require('Config.php');
		$token = $_POST['token'];

		$sql_test_token = "SELECT * FROM user WHERE token = '$token'";
		$result_test_token = mysqli_query($conn, $sql_test_token);
		if (mysqli_num_rows($result_test_token) == 0) {
			$code = "9998";
			$message = "Token is invalid";
		} else {
			if ($conn == null) {
				$code = '1001';
				$message = 'Can not connect to DB.';
			} else {
				$data = array();
				require('utils.php');

				$token = $_POST['token'];
				$user_id = getUserByToken($token);
				$sql = "SELECT * FROM address WHERE user_id = '$user_id'";
				$result = mysqli_query($conn, $sql);
				while ($row = mysqli_fetch_assoc($result)) {
					$data_element = array();
					$data_element['id'] = $row['id'];
					$data_element['address'] = $row['address'];
					$data_element['address_id'] = explode("-", $row['address_id']);
					$data_element['default'] = $row['is_default'];
					array_push($data, $data_element);
				}

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