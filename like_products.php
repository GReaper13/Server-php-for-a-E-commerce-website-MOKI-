<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!isset($_POST['token']) || !isset($_POST['product_id'])) {
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
				$product_id = $_POST['product_id'];
				$user_id = getUserByToken($token);

				$sql_search = "SELECT * FROM tb_like WHERE iduser = '$user_id' AND idproduct = '$product_id'";
				$result_search = mysqli_query($conn, $sql_search);
				if (mysqli_num_rows($result_search) == 0) {
					$sql = "INSERT INTO tb_like(id, iduser, idproduct) VALUES (NULL, '$user_id', '$product_id')";
					$result = mysqli_query($conn, $sql);
				} else {
					$sql = "DELETE FROM tb_like WHERE iduser = '$user_id' AND idproduct = '$product_id'";
					$result = mysqli_query($conn, $sql);
				}

				// get count like of product after user like
				$sql_receive = "SELECT COUNT(id) FROM tb_like WHERE idproduct = '$product_id'";
				$result_receive = mysqli_query($conn, $sql_receive);
				$row_receive = mysqli_fetch_assoc($result_receive);
				$like = $row_receive['COUNT(id)'];
				$data['like'] = $like;

				$code = '1000';
				$message = 'OK';

				//create notification on notice table
				$sql_search_notice = "SELECT * FROM notice WHERE object_id = '$product_id' AND object_user_id = '$user_id' AND type = 'like_product'";
				$result_search_notice = mysqli_query($conn, $sql_search_notice);
				if (mysqli_num_rows($result_search_notice) == 0) {
					$type = "like_product";
					$object_id = $product_id;
					$created = getTimeInString();

					//get profile of user
					$sql_user = "SELECT * FROM user WHERE id = '$user_id'";
					$result_user = mysqli_query($conn, $sql_user);
					$row_user = mysqli_fetch_assoc($result_user);
					$username = $row_user['username'];
					$avatar = $row_user['avatar'];
					

					// get seller_id of product
					$sql_seller = "SELECT * FROM products WHERE id = '$product_id'";
					$result_seller = mysqli_query($conn, $sql_seller);
					$row_seller = mysqli_fetch_assoc($result_seller);
					$seller_id = $row_seller['seller_id'];
					$product_name = $row_seller['name'];
					$title = $username." đã thích ".$product_name." của bạn. ";


					$sql_notice = "INSERT INTO notice(id, type, object_id, title, created, avatar, has_group, is_read, user_id, object_user_id) 
					VALUES (NULL, '$type', '$object_id', '$title', '$created', '$avatar', '0', '0', '$seller_id', '$user_id')";
					$result_notice = mysqli_query($conn, $sql_notice);
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
if (isset($data)) {
	$array['data'] = $data;
}
$ouput = json_encode($array);
echo $ouput;
?>