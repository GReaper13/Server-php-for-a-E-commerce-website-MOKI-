<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!isset($_POST['count']) || !isset($_POST['user_id']) || !isset($_POST['index'])) {
		$code = "1002";
		$message = 'Parameter is not enought.';
	} else {
		require('Config.php');
		if ($conn == null) {
			$code = '1001';
			$message = 'Can not connect to DB.';
		} else {
			if (isset($_POST['token'])) {
				$token = $_POST['token'];

				$sql_test_token = "SELECT * FROM user WHERE token = '$token'";
				$result_test_token = mysqli_query($conn, $sql_test_token);
				if (mysqli_num_rows($result_test_token) == 0) {
					$code = "9998";
					$message = "Token is invalid";
					$flag = false;
				} else {
					$flag = true;
				}
			} else {
				$token = "0";
				$flag = true;
			}

			if ($flag) {

				$data = array();
				require('utils.php');

				$user_id = $_POST['user_id'];
				$count = $_POST['count'];
				$index = $_POST['index'];
				$current_id = getUserByToken($token);

				$count = (int)$count;
				$index = (int)$index;
				$sql = "SELECT * FROM relative WHERE object_id = '$user_id' AND type = 'follow' ORDER BY id DESC LIMIT $index, $count";
				$result = mysqli_query($conn, $sql);
				
				while ($row = mysqli_fetch_assoc($result)) {
					$data_element = array();
					$id = $row['own_id'];
					$sql_search = "SELECT * FROM user WHERE id = '$id' ";
					$result_seach = mysqli_query($conn, $sql_search);
					$row_search = mysqli_fetch_assoc($result_seach);
					$data_element['id'] = $row_search['id'];
					$data_element['username'] = $row_search['username'];
					$data_element['avatar'] = $row_search['avatar'];

					//get followed
					$sql_followed = "SELECT * FROM relative WHERE own_id = '$current_id' AND object_id = '$id' AND type = 'follow'";
					$result_followed = mysqli_query($conn, $sql_followed);
					if (mysqli_num_rows($result_followed) == 0) {
						$followed = "0";
					} else {
						$followed = "1";
					}
					$data_element['followed'] = $followed;

					array_push($data, $data_element);
				}
				$code = "1000";
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