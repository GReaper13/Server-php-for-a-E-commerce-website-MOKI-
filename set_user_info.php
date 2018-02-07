<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!isset($_POST['token']) || !isset($_POST['username']) || !isset($_POST['status'])) {
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
				$data = array();

				$token = $_POST['token'];
				$username = $_POST['username'];
				$status = $_POST['status'];
				if (isset($_POST['email'])) {
					$email = $_POST['email'];
				} else {
					$email = ""; 
				}

				$user_id = getUserByToken($token);
				$sql_search = "SELECT * FROM user WHERE id = '$user_id'";
				$result_search = mysqli_query($conn, $sql_search);
				$row_search = mysqli_fetch_assoc($result_search);
				$current_avatar = $row_search['avatar'];
				$current_cover = $row_search['cover'];

				if (isset($_FILES['avatar'])) {
					move_uploaded_file($_FILES['avatar']['tmp_name'], './file/'.$_FILES['avatar']['name']);
					$new_avatar = DOMAIN."/file/".$_FILES['avatar']['name'];
					if ($current_avatar != DOMAIN.'/file/default-avatar.png') {
						deleteFile($current_avatar, DOMAIN);
					}
				} else {
					$new_avatar = $current_avatar;
				}

				if (isset($_FILES['cover'])) {
					move_uploaded_file($_FILES['cover']['tmp_name'], './file/'.$_FILES['cover']['name']);
					$new_cover = DOMAIN."/file/".$_FILES['cover']['name'];
					if ($current_cover != DOMAIN.'/file/default-cover.jpg') {
						deleteFile($current_cover, DOMAIN);
					}
				} else {
					$new_cover = $current_cover;
				}

				$sql = "UPDATE user SET username = '$username', avatar = '$new_avatar', cover = '$new_cover', email = '$email',
				status = '$status' WHERE id = '$user_id'";
				$result = mysqli_query($conn, $sql);

				$data['avatar'] = $new_avatar;
				$data['cover'] = $new_cover;
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