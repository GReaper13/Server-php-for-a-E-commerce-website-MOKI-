<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!isset($_POST['token']) || !isset($_POST['count']) || !isset($_POST['index']) || !isset($_POST['group'])) {
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

				$token = $_POST['token'];
				$index = $_POST['index'];
				$count = $_POST['count'];
				$group = $_POST['group'];
				$index = (int)$index;
				$count = (int)$count;

				$user_id = getUserByToken($token);
				$sql = "SELECT * FROM notice WHERE user_id = '$user_id' AND has_group = '$group' ORDER BY id DESC LIMIT $index,$count";
				$result = mysqli_query($conn, $sql);
				while ($row = mysqli_fetch_assoc($result)) {
					$object_id = $row['object_id'];
					$data_element = array();
					$data_element['id'] = $row['id'];
					$data_element['type'] = $row['type'];
					$data_element['object_id'] = $row['object_id'];
					$data_element['title'] = $row['title'];
					$data_element['create'] = $row['created'];

					// get image or thumb of image
					$sql_img = "SELECT * FROM file_product WHERE id_product = '$object_id' ORDER BY id ASC LIMIT 1";
					$result_img = mysqli_query($conn, $sql_img);
					$row_img = mysqli_fetch_assoc($result_img);
					if ($row_img['type'] == 'image') {
						$data_element['avatar'] = $row_img['url'];
					} else {
						$data_element['avatar'] = $row_img['thumbnails'];
					}

					$data_element['read'] = $row['is_read'];

					array_push($data, $data_element);
				}

				$code = '1000';
				$message = 'OK';
				$sql_badge = "SELECT COUNT(id) FROM notice WHERE user_id = '$user_id' AND is_read = '0'";
				$result_badge = mysqli_query($conn, $sql_badge);
				$row_badge = mysqli_fetch_assoc($result_badge);
				$badge = $row_badge['COUNT(id)'];
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
if (isset($badge)) {
	$array['badge'] = $badge;
}
$ouput = json_encode($array);
echo $ouput;
?>