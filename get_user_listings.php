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
				$sql = "SELECT * FROM products WHERE seller_id = '$user_id' ORDER BY id DESC LIMIT $index, $count";
				$result = mysqli_query($conn, $sql);
				
				while ($row = mysqli_fetch_assoc($result)) {
					$data_element = array();
					$data_element['id'] = $row['id'];
					$data_element['name'] = $row['name'];
					$data_element['price'] = $row['price'];
					$data_element['price_percent'] = $row['price_percent'];
					$data_element['described'] = $row['described'];
					$data_element['created'] = $row['created'];
					$data_element['banned'] = $row['banned'];

					// get image or video product
					$id = $row['id'];
					$sql_file = "SELECT * FROM file_product WHERE id_product = '$id'";
					$result_file = mysqli_query($conn, $sql_file);
					$row_file = mysqli_fetch_assoc($result_file);
					if ($row_file['type'] == 'image') {
						$image = array();
						array_push($image, $row_file['url']);
						while ($row_file = mysqli_fetch_assoc($result_file)) {
							array_push($image, $row_file['url']);
						}
						$data_element['image'] = $image;
					} else {
						$video = array();
						$video['url'] = $row_file['url'];
						$video['thumb'] = $row_file['thumbnails'];
						$data_element['video'] = $video;
					}

					//get islike
					$sql_isliked = "SELECT * FROM tb_like WHERE iduser = '$current_id' AND idproduct = '$id'";
					$result_islike = mysqli_query($conn, $sql_isliked);
					if (mysqli_num_rows($result_islike) == 0) {
						$islike = "0";
					} else {
						$islike = "1";
					}
					$data_element['is_liked'] = $islike;

					// get number of like of product
					$sql_like = "SELECT COUNT(iduser) FROM tb_like WHERE idproduct = '$id'";
					$result_like = mysqli_query($conn, $sql_like);
					$row_like = mysqli_fetch_assoc($result_like);
					$like = $row_like['COUNT(iduser)'];
					$data_element['like'] = $like;

					// get number of comment of product
					$sql_cmt = "SELECT COUNT(poster_id) FROM comment WHERE product_id = '$id'";
					$result_cmt = mysqli_query($conn, $sql_cmt);
					$row_cmt = mysqli_fetch_assoc($result_cmt);
					$cmt = $row_cmt['COUNT(poster_id)'];
					$data_element['comment'] = $cmt;

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