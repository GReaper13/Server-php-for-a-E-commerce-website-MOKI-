<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!isset($_POST['token']) || !isset($_POST['id'])) {
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
				require('utils.php');

				$token = $_POST['token'];
				$id = $_POST['id'];

				$sql = "DELETE FROM  address WHERE id = '$id'";
				$result = mysqli_query($conn, $sql);
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
$ouput = json_encode($array);
echo $ouput;
?>