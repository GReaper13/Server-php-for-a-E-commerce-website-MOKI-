<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!isset($_POST['token']) || !isset($_POST['address']) || !isset($_POST['address_id']) || !isset($_POST['default'])) {
		$code = "1002";
		$message = 'Parameter is not enought.';
	} else {
		require('Config.php');
		if ($conn == null) {
			$code = '1001';
			$message = 'Can not connect to DB.';
		} else {
			require('utils.php');

			$token = $_POST['token'];

			$sql_test_token = "SELECT * FROM user WHERE token = '$token'";
			$result_test_token = mysqli_query($conn, $sql_test_token);
			if (mysqli_num_rows($result_test_token) == 0) {
				$code = "9998";
				$message = "Token is invalid";
			} else {
				$address = $_POST['address'];
				$default = $_POST['default'];
				$address_id = implode("-", $_POST['address_id']);
				$user_id = getUserByToken($token);

				$sql_search = "SELECT * FROM address WHERE user_id = '$user_id' AND address = '$address'";
				$result_search = mysqli_query($conn, $sql_search);
				if (mysqli_num_rows($result_search) != 0) {
					$code = "1015";
					$message = "Address is exist";
				} else {
					if ($default == '1') {
						$sql_receiver = "UPDATE address SET is_default = '0' WHERE is_default = '1' ";
						$result_receiver = mysqli_query($conn, $sql_receiver);
					}
					$sql = "INSERT INTO address(id,     user_id,    address,    address_id, is_default) 
					VALUES (null, '$user_id', '$address', '$address_id', '$default')";
					$result = mysqli_query($conn, $sql);
					$code = "1000";
					$message = "OK";
				}
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
$ouput = json_encode($array);
echo $ouput;
?>