<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!isset($_POST['product_id'])) {
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

				$product_id = $_POST['product_id'];


				$sql = "SELECT * FROM comment WHERE product_id = '$product_id'";
				$result = mysqli_query($conn, $sql);
				while ($row = mysqli_fetch_assoc($result)) {
					$data_element = array();
					$data_element['id'] = $row['id'];
					$data_element['comment'] = $row['comment'];
					$data_element['created'] = $row['created'];

				// get profile of poster
					$poster = array();
					$poster_id = $row['poster_id'];
					$sql_poster = "SELECT * FROM user WHERE id = '$poster_id'";
					$result_poster = mysqli_query($conn, $sql_poster);
					$row_poster = mysqli_fetch_assoc($result_poster);
					$poster['id'] = $row_poster['id'];
					$poster['name'] = $row_poster['username'];
					$poster['avatar'] = $row_poster['avatar'];
					$data_element['poster'] = $poster;

					array_push($data, $data_element);
				}

				$sql_seller = "SELECT * FROM products WHERE id = '$product_id'";
				$result_seller = mysqli_query($conn, $sql_seller);
				$row_seller = mysqli_fetch_assoc($result_seller);
				$seller_id = $row_seller['seller_id'];

				$user_id = getUserByToken($token);
				$sql_isblocked = "SELECT * FROM relative WHERE own_id = '$seller_id'  AND object_id = '$user_id' AND type = 'block'";
				$result_isblocked  = mysqli_query($conn, $sql_isblocked);
				if (mysqli_num_rows($result_isblocked) == 0) {
					$is_blocked = "0";  
				} else {
					$is_blocked = "1";
				}

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
if (isset($is_blocked)) {
	$array['is_blocked'] = $is_blocked;
}
$ouput = json_encode($array);
echo $ouput;
?>