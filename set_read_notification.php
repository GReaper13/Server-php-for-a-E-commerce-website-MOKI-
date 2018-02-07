<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!isset($_POST['token']) || !isset($_POST['notification_id'])) {
		$code = "1002";
		$message = 'Parameter is not enought.';
	} else {
		require('Config.php');
		if ($conn == null) {
			$code = '1001';
			$message = 'Can not connect to DB.';
		} else {
			$token = $_POST['token'];

			$sql_test_token = "SELECT * FROM user WHERE token = '$token'";
			$result_test_token = mysqli_query($conn, $sql_test_token);
			if (mysqli_num_rows($result_test_token) == 0) {
				$code = "9998";
				$message = "Token is invalid";
			} else {
				$data = array();
				require('utils.php');

				$user_id = getUserByToken($token);
				$notification_id = $_POST['notification_id'];

				$sql = "UPDATE notice SET is_read = '1' WHERE id = '$notification_id'";
				$result = mysqli_query($conn, $sql);

				// find badge
				$sql_badge = "SELECT COUNT(id) FROM notice WHERE user_id = '$user_id' AND is_read = '0'";
				$result_badge = mysqli_query($conn, $sql_badge);
				$row_badge = mysqli_fetch_assoc($result_badge);
				$badge = $row_badge['COUNT(id)'];
				$data['badge'] = $badge;

				$code = '1000';
				$message = 'OK';
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