<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!isset($_POST['token']) || !isset($_POST['like']) || !isset($_POST['comment']) || !isset($_POST['announcement']) || !isset($_POST['sound_on']) || !isset($_POST['sound_default'])) {
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
				require('utils.php');

				$token = $_POST['token'];
				$user_id = getUserByToken($token);

				$like = $_POST['like'];
				$comment = $_POST['comment'];
				$announcement = $_POST['announcement'];
				$sound_on = $_POST['sound_on'];
				$sound_default = $_POST['sound_default'];

				$sql = "UPDATE push SET like_on = '$like', comment_on = '$comment', announcement = '$announcement', sound_on = '$sound_on', sound_default = '$sound_default' WHERE user_id = '$user_id'";
				$result = mysqli_query($conn, $sql);
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