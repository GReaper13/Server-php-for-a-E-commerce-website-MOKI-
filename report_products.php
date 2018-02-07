<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!isset($_POST['token']) || !isset($_POST['product_id']) || !isset($_POST['subject']) || !isset($_POST['details'])) {
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
				
				$product_id = $_POST['product_id'];
				$token = $_POST['token'];
				$subject = $_POST['subject'];
				$details = $_POST['details'];
				
				$id_report = getUserByToken($token);

				$sql_search = "SELECT * FROM report WHERE id_report = '$id_report' AND product_id = '$product_id'";
				$result_search = mysqli_query($conn, $sql_search);
				if (mysqli_num_rows($result_search) == 0) {
					$sql = "INSERT INTO report(id,    product_id, id_report,        subject,    details,    token) 
					VALUES (NULL, '$product_id', '$id_report', '$subject', '$details', '$token')";
					$result = mysqli_query($conn, $sql);

					$code = '1000';
					$message = 'OK';
				} else {
					$code = '1010';
					$message = 'action has been done previously by this user.';
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