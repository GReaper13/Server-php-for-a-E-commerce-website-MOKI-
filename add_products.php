<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!isset($_POST['name']) || !isset($_POST['price']) || !isset($_POST['category_id']) || !isset($_POST['ships_from']) || !isset($_POST['ships_from_id']) || !isset($_POST['condition']) || !isset($_POST['token'])) {
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
					$dimension = "";
				}
				if (isset($_POST['weight'])) {
					$weight = $_POST['weight']; 
				} else {
					$weight = "";
				}

				// get seller id by token
				$seller_id = getUserByToken($token);

				$price_percent = 0;

				$created = getTimeInString();
				$url = "www.moki.com" ; 

				$sql = "INSERT INTO products(id,    name,   price,  price_percent, described, ships_from, ships_from_id,
				condition_p,    dimension,    weight,    created,
				seller_id,      category_id,   url,      token,  size_id,              brand_id) 
				VALUES (NULL,'$name','$price','$price_percent', '$described', '$ships_from', '$ships_from_id',
				'$condition', '$dimension', '$weight', '$created',
				'$seller_id', '$category_id', '$url', '$token', '$product_size_id', '$brand_id')";
				$result = mysqli_query($conn, $sql);

				$sql_receive = "SELECT * FROM products WHERE token='$token' ORDER BY id DESC LIMIT 1";
				$result_receive = mysqli_query($conn, $sql_receive);
				$row = mysqli_fetch_assoc($result_receive);
				$id = $row['id'];
				$data['id'] = $id;
				$data['url'] = $url;

				$code = '1000';
				$message = 'OK';

			    // load image/video to server
				if (isset($_FILES['image'])) {
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