<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!isset($_POST['token']) || !isset($_POST['name']) || !isset($_POST['price']) || !isset($_POST['category_id']) || !isset($_POST['ships_from']) || !isset($_POST['ships_from_id']) || !isset($_POST['condition'])) {
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
				$id = $_POST['id'];
				$name = $_POST['name'];
				$category_id = $_POST['category_id'];
				$ships_from = $_POST['ships_from'];
				$ships_from_id = implode('-', $_POST['ships_from_id']);
				$condition = $_POST['condition'];
				$price = $_POST['price'];

				if (isset($_POST['product_size_id'])) {
					$product_size_id = $_POST['product_size_id'];
				} else {
					$product_size_id = 0;
				}
				if (isset($_POST['brand_id'])) {
					$brand_id = $_POST['brand_id'];
				} else {
					$brand_id = 0;
				}
				if (isset($_POST['described'])) {
					$described = $_POST['described'];
				} else {
					$described = "";
				}
				if (isset($_POST['dimension'])) {
					$dimension = implode('x', $_POST['dimension']);
				} else {
					$dimension = 0;
				}
				if (isset($_POST['weight'])) {
					$weight = $_POST['weight']; 
				} else {
					$weight = 0;
				}

				$sql = "UPDATE products SET token = '$token', name = '$name', price = '$price', ships_from = '$ships_from',
				ships_from_id = '$ships_from_id', condition_p = '$condition', category_id = '$category_id',
				described = '$described',
				dimension = '$dimension', weight = '$weight' 
				WHERE id='$id'";
				$result = mysqli_query($conn, $sql);

			    // load image/video to server and del video if user upload image
				if (isset($_FILES['image'])) {
					$sql_search_video = "SELECT * FROM file_product WHERE id_product = '$id'";
					$result_search_video = mysqli_query($conn, $sql_search_video);
					//var_dump($sql_search_video);
					if(mysqli_num_rows($result_search_video) == 1) {
						$row_search_video = mysqli_fetch_assoc($result_search_video);
						if ($row_search_video['type'] == 'video') {
							$sql_del_video = "DELETE FROM file_product WHERE id_product = '$id'";
							mysqli_query($conn, $sql_del_video);
							$url_video = $row_search_video['url'];
							$url_thumb = $row_search_video['thumbnails'];
							deleteFile($url_video, DOMAIN);
							deleteFile($url_thumb, DOMAIN);
						}
					}
					for ($i=0; $i < count($_FILES['image']['name']) ; $i++) { 
						move_uploaded_file($_FILES['image']['tmp_name'][$i], './file/'.$_FILES['image']['name'][$i]);
						$url_img = DOMAIN."/file/".$_FILES['image']['name'][$i];
						$sql_img = "INSERT INTO file_product(id, url, thumbnails, type, id_product)
						VALUES (NULL, '$url_img', '', 'image', '$id')";
						$resul_img = mysqli_query($conn, $sql_img);
					}
				} else if (isset($_FILES['video'])) {
					move_uploaded_file($_FILES['thumb']['tmp_name'], './file/'.$_FILES['thumb']['name']);
					move_uploaded_file($_FILES['video']['tmp_name'], './file/'.$_FILES['video']['name']);
					$url_vid = DOMAIN."/file/".$_FILES['video']['name'];
					$url_thumb = DOMAIN."/file/".$_FILES['thumb']['name'];
					$sql_vid = "INSERT INTO file_product(id, url, thumbnails, type, id_product)
					VALUES (NULL, '$url_vid', '$url_thumb', 'video', '$id')";
					$resul_vid = mysqli_query($conn, $sql_vid);
				}
				//delete image from array 
				if (isset($_POST['image_del'])) {
					$image_del = $_POST['image_del'];
					for ($i=0; $i < count($image_del); $i++) { 
						$id_image = $image_del[$i];
						$sql_search_img = "SELECT * FROM file_product WHERE id_product = '$id' AND type = 'image'";
						$result_search_img = mysqli_query($conn, $sql_search_img);
						while ($row_search_img = mysqli_fetch_assoc($result_search_img)) {
							deleteFile($row_search_img['url'], DOMAIN);
						}
						$sql_del_img = "DELETE FROM file_product WHERE id = '$id_image'";
						mysqli_query($conn, $sql_del_img);
					}
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
$ouput = json_encode($array);
echo $ouput;
?>