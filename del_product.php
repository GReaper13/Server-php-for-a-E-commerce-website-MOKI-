<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!isset($_POST['token']) || !isset($_POST['id'])) {
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
				require('utils.php');

				$token = $_POST['token'];
				$id = $_POST['id'];

				$sql_search = "SELECT * FROM products WHERE id = '$id'";
				$result_search = mysqli_query($conn, $sql_search);
				if (mysqli_num_rows($result_search) == 0) {
					$code = "9992";
					$message = "Product is not existed";
				} else {
					$sql = "DELETE FROM products WHERE id = '$id'";
					mysqli_query($conn, $sql);
					
					$sql_like = "DELETE FROM tb_like WHERE idproduct = '$id'";
					mysqli_query($conn, $sql_like);
					
					$sql_cmt = "DELETE FROM comment WHERE product_id = '$id'";
					mysqli_query($conn, $sql_cmt);
					
					// del video or image
					$sql_search_file = "SELECT * FROM file_product WHERE id_product = '$id'";
					$result_search_file = mysqli_query($conn, $sql_search_file);
					while ($row = mysqli_fetch_assoc($result_search_file)) {
						deleteFile($row['url'], DOMAIN);
						if ($row['type'] == 'video') {
							deleteFile($row['thumbnails'], DOMAIN);
						}
					}
					$sql_del_file = "DELETE FROM file_product WHERE id_product = '$id'";
					mysqli_query($conn, $sql_del_file);

					$code = "1000";
					$message = "OK";
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
	'message' => $message,
);
$ouput = json_encode($array);
echo $ouput;
?>