<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!isset($_POST['count']) || !isset($_POST['index']) || !isset($_POST['category_id'])) {
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
				
				require('utils.php');
				$data = array();
				$product = array();
				$count = $_POST['count'];
				$category_id = $_POST['category_id'];
				$index = $_POST['index'];
				$new_items = 0;

				$category = array();
				$test = array();
				$test['id'] = $category_id;
				$test['check'] = 0;
				array_push($category, $test);

				while (true) {
					if (check($category) == -1) {
						break;
					} else {
						$index_check = check($category);
						$category_id_temp = $category[$index_check]["id"];
					}
					$sql_get_all = "SELECT * FROM category WHERE parent_id = '$category_id_temp'";
					$result_get_all = mysqli_query($conn, $sql_get_all);
					while ($row_get_all = mysqli_fetch_assoc($result_get_all)) {
						$category_element = array();
						$category_element['id'] = $row_get_all['id'];
						$category_element['check'] = 0;
						array_push($category, $category_element);
					}
					$category[$index_check]["check"] = 1;
				}

				$add_condition = "category_id = '$category_id'";

				for ($i=1; $i < count($category); $i++) { 
					$category_id_temp2 = $category[$i]['id'];
					$add_condition = $add_condition." OR category_id = '$category_id_temp2'";
				}

				if (empty($category_id)) {
					$sql = "SELECT * FROM products ORDER BY id DESC";
				} else {
					$sql = "SELECT * FROM products WHERE $add_condition ORDER BY id DESC";
				}
				$result = mysqli_query($conn, $sql);
				$row = mysqli_fetch_assoc($result);				

				if (!isset($_POST['last_id'])) {
					$last_id = $row['id'];
				} else {
					$last_id = $_POST['last_id'];
					if ($last_id != $row['id']) {
						$temp1 = (int) $last_id;
						$temp2 = (int) $row['id'];
						$new_items = $temp2 - $temp1;
					}
				}

				$index = (int) $index;
				$index += $new_items;
				$count = (int) $count;

				if (empty($category_id)) {
					$sql = "SELECT * FROM products ORDER BY id DESC LIMIT $index,$count";
				} else {
					$sql = "SELECT * FROM products WHERE $add_condition ORDER BY id DESC LIMIT $index,$count";
				}

				$result = mysqli_query($conn, $sql);
				while ($row = mysqli_fetch_assoc($result)) {
					$product_element = array();
					$product_element['id'] = $row['id'];
					$product_element['name'] = $row['name'];
					$product_element['price'] = $row['price'];
					$product_element['price_percent'] = $row['price_percent'];
					$product_element['described'] = $row['described'];
					$product_element['created'] = $row['created'];
					$product_element['banned'] = $row['banned'];

					// get seller profile
					$seller = array();
					$seller['id'] = $row['seller_id'];
					$seller_id = $row['seller_id'];
					$sql_temp = "SELECT * FROM user WHERE id = $seller_id";
					$result_temp = mysqli_query($conn, $sql_temp);
					$row_temp = mysqli_fetch_assoc($result_temp);
					$seller['username'] = $row_temp['username'];
					$seller['avatar'] = $row_temp['avatar'];
					$product_element['seller'] = $seller;

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
						$product_element['image'] = $image;
					} else {
						$video = array();
						$video['url'] = $row_file['url'];
						$video['thumb'] = $row_file['thumbnails'];
						$product_element['video'] = $video;
					}

					// get can edit by compare between id user in token and id seller
					$user_id = getUserByToken($token);
					if ($seller_id == $user_id) {
						$can_edit = "1"; 
					} else {
						$can_edit = "0";
					}
					$product_element['can_edit'] = $can_edit;

					// get brand name
					$brand_id = $row['brand_id'];
					$sql_brand = "SELECT * FROM brand WHERE id = '$brand_id'";
					$result_brand = mysqli_query($conn, $sql_brand);
					$row_brand = mysqli_fetch_assoc($result_brand);
					if ($row_brand != NULL) {
						$brand = $row_brand['name'];
						$product_element['brand'] = $brand;
					}

					//get islike
					$sql_isliked = "SELECT * FROM tb_like WHERE iduser = '$user_id' AND idproduct = '$id'";
					$result_islike = mysqli_query($conn, $sql_isliked);
					if (mysqli_num_rows($result_islike) == 0) {
						$islike = "0";
					} else {
						$islike = "1";
					}
					$product_element['is_liked'] = $islike;

					// get is_blocked
					$sql_isblocked = "SELECT * FROM relative WHERE own_id = '$seller_id' AND object_id = '$user_id' AND type = 'block'";
					$result_isblocked = mysqli_query($conn, $sql_isblocked);
					if (mysqli_num_rows($result_isblocked) == 0) {
						$isblocked = "0";
					} else {
						$isblocked = "1";
					}
					$product_element['is_blocked'] = $isblocked;

					// get number of like of product
					$sql_like = "SELECT COUNT(iduser) FROM tb_like WHERE idproduct = '$id'";
					$result_like = mysqli_query($conn, $sql_like);
					$row_like = mysqli_fetch_assoc($result_like);
					$like = $row_like['COUNT(iduser)'];
					$product_element['like'] = $like;

					// get number of comment of product
					$sql_cmt = "SELECT COUNT(poster_id) FROM comment WHERE product_id = '$id'";
					$result_cmt = mysqli_query($conn, $sql_cmt);
					$row_cmt = mysqli_fetch_assoc($result_cmt);
					$cmt = $row_cmt['COUNT(poster_id)'];
					$product_element['comment'] = $cmt;

					array_push($product, $product_element);
				}
				$data['products'] = $product;
				$data['new_items'] = $new_items;
				$data['last_id'] = $last_id;
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