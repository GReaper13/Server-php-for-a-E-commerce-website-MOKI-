<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!isset($_POST['token']) || !isset($_POST['count']) || !isset($_POST['index'])) {
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
				$count = $_POST['count'];
				$index = $_POST['index'];

				$user_id = getUserByToken($token);
				$count = (int) $count;
				$index = (int) $index;

				$sql = "SELECT * FROM tb_like WHERE iduser = '$user_id' ORDER BY id DESC LIMIT $index,$count" ;
				$result = mysqli_query($conn, $sql);
				while ($row = mysqli_fetch_assoc($result)) {
					$data_element = array();
					$data_element['id'] = $row['idproduct'];
					$product_id = $row['idproduct'];

					// get infor of product
					$sql_search = "SELECT * FROM products WHERE id = '$product_id'";
					$result_search = mysqli_query($conn, $sql_search);
					$row_search = mysqli_fetch_assoc($result_search);
					$data_element['name'] = $row_search['name'];
					$data_element['price'] = $row_search['price'];

					// get image or thumb of image
					$sql_img = "SELECT * FROM file_product WHERE id_product = '$product_id' ORDER BY id ASC LIMIT 1";
					$result_img = mysqli_query($conn, $sql_img);
					$row_img = mysqli_fetch_assoc($result_img);
					if ($row_img['type'] == 'image') {
						$data_element['image'] = $row_img['url'];
					} else {
						$data_element['image'] = $row_img['thumbnails'];
					}

					array_push($data, $data_element);
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
$ouput = json_encode($array);
echo $ouput;
?>