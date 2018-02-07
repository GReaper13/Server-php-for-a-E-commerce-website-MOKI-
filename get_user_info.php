<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!isset($_POST['user_id'])) {
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

				$current_id = getUserByToken($token);

				$sql = "SELECT * FROM user WHERE id = '$user_id'";
				$result = mysqli_query($conn, $sql);
				$row = mysqli_fetch_assoc($result);

				$data['id'] = $row['id'];
				$data['username'] = $row['username'];
				$data['url'] = $row['url'];
				$data['status'] = $row['status'];
				$data['avatar'] = $row['avatar'];
				$data['cover'] = $row['cover'];
				$data['email'] = $row['email'];
				$data['score'] = $row['score'];
				$data['online'] = $row['online'];

				//get listing of seller
				$sql_listing = "SELECT COUNT(id) FROM products WHERE seller_id = '$user_id'";
				$result_listing = mysqli_query($conn,$sql_listing);
				$row_listing = mysqli_fetch_assoc($result_listing);
				$listing = $row_listing['COUNT(id)'];
				$data['listing'] = $listing;

				if ($user_id != $current_id) {
				// get is_blocked
					$sql_isblocked = "SELECT * FROM relative WHERE own_id = '$user_id' AND object_id = '$current_id' AND type = 'block'";
					$result_isblocked = mysqli_query($conn, $sql_isblocked);
					if (mysqli_num_rows($result_isblocked) == 0) {
						$isblocked = "0";
					} else {
						$isblocked = "1";
					}
					$data['is_blocked'] = $isblocked;

				// get followed
					$sql_followed = "SELECT * FROM relative WHERE own_id = '$current_id' AND object_id = '$user_id' AND type = 'follow'";
					$result_followed = mysqli_query($conn, $sql_followed);
					if (mysqli_num_rows($result_followed) == 0) {
						$followed = "0";
					} else {
						$followed = "1";
					}
					$data['followed'] = $followed;
				} else {
					$sql_address = "SELECT * FROM address WHERE user_id = '$user_id' AND is_default = '1'";
					$result_address = mysqli_query($conn, $sql_address);
					if (mysqli_num_rows($result_address) != 0) {
						$row_address = mysqli_fetch_assoc($result_address);
						$default_address = array();
						$default_address['address'] = $row_address['address'];
						$address_id = explode("-", $row_address['address_id']);
						$default_address['address_id'] = $address_id;
						$data['default_address'] = $default_address;
					} 
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