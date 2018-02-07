<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!isset($_POST['token']) || !isset($_POST['product_id']) || !isset($_POST['comment']) || !isset($_POST['index'])) {
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
				$data = array();
				require('utils.php');

				$product_id = $_POST['product_id'];
				$comment = $_POST['comment'];
				$index = $_POST['index'];
				$index = (int)$index ;

				$poster_id_new = getUserByToken($token);
				$created = getTimeInString();

				$sql = "INSERT INTO comment(id, product_id, poster_id, comment, created) 
				VALUES (NULL, '$product_id', '$poster_id_new', '$comment', '$created')";

				mysqli_query($conn, $sql);

				// count comment
				$sql_commment = "SELECT COUNT(id) FROM comment WHERE product_id = '$product_id'";
				$result_comment = mysqli_query($conn, $sql_commment);
				$row_comment = mysqli_fetch_assoc($result_comment);
				$count = $row_comment['COUNT(id)'];
				$count = (int)$count - $index;

				$sql_receive = "SELECT * FROM comment WHERE product_id = '$product_id' LIMIT $index, $count";
				$result_receive = mysqli_query($conn,$sql_receive);
				while ($row_receive = mysqli_fetch_assoc($result_receive)) {
					$data_element = array();
					
					// get profile of poster
					$poster_id = $row_receive['poster_id'];
					$poster = array();
					$sql_poster = "SELECT * FROM user WHERE id = '$poster_id'";
					$result_poster = mysqli_query($conn, $sql_poster);
					$row_poster = mysqli_fetch_assoc($result_poster);
					$poster['id'] = $row_poster['id'];
					$poster['name'] = $row_poster['username'];
					$poster['avatar'] = $row_poster['avatar'];

					$data_element['id'] = $row_receive['id'];
					$data_element['comment'] = $row_receive['comment'];
					$data_element['created'] = $row_receive['created'];
					$data_element['poster'] = $poster;
					array_push($data, $data_element);
				}

				$code = '1000';
				$message = 'OK';

				//create notification on notice table
				
				$type = "comment_product";
				$object_id = $product_id;

					//get profile of user
				$sql_user = "SELECT * FROM user WHERE id = '$poster_id_new'";
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
				$title = $username." cũng đã bình luận trong ".$product_name." của bạn. ";

				$sql_notice = "INSERT INTO notice(id, type, object_id, title, created, avatar, has_group, is_read, user_id, object_user_id) 
				VALUES (NULL, '$type', '$object_id', '$title', '$created', '$avatar', '0', '0', '$seller_id', '$poster_id_new')";
				$result_notice = mysqli_query($conn, $sql_notice);
				
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