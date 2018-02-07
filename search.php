<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!isset($_POST['index']) || !isset($_POST['count']) ) {
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
				$index = (int)$_POST['index'];
				$count = (int)$_POST['count'];
				$user_id = getUserByToken($token);



				if (isset($_POST['category_id'])) {
					$category_id = $_POST['category_id'];
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
					$has_category = " AND ($add_condition)";
				} else {
					$has_category = "";
				}

				if (isset($_POST['brand_id'])) {
					$brand_id = $_POST['brand_id'];
					$has_brand = " AND brand_id = '$brand_id'";
				} else {
					$has_brand = "";
				}
				if (isset($_POST['product_size_id'])) {
					$product_size_id = $_POST['product_size_id'];
					$has_size = " AND size_id = '$product_size_id'";
				} else {
					$has_size = "";
				}
				if (isset($_POST['condition'])) {
					$condition = $_POST['condition'];
					$has_condition = " AND condition_p = '$condition'";
				} else {
					$has_condition = "";
				}
				if (isset($_POST['keyword'])) {
					$keyword = $_POST['keyword'];
					$has_keyword = " AND (name LIKE '%{$keyword}%' OR username LIKE '%{$keyword}%' OR described LIKE '%{$keyword}%')";
				} else {
					$has_keyword = "";
				}
				if (isset($_POST['price_min'])) {
					$price_min = (int)$_POST['price_min'];
				} else {
					$price_min = 0;
				}
				if (isset($_POST['price_max'])) {
					$price_max = (int)$_POST['price_max'];
				} else {
					$price_max = 99999999;
				}

				$sql = "SELECT * FROM products LEFT JOIN user ON products.seller_id = user.id WHERE price_percent LIKE '%%'".$has_category.$has_condition.$has_size.$has_brand.$has_keyword." ORDER BY products.id DESC LIMIT $index, $count";
				$result = mysqli_query($conn, $sql);
				while ($row = mysqli_fetch_assoc($result)) {
					$price = (int)$row['price'];
					if ($price >= $price_min && $price <= $price_max) {
						$data_element = array();
						$data_element['id'] = $row['id'];
						$data_element['name'] = $row['name'];
						$data_element['price'] = $row['price'];
						$data_element['price_percent'] = $row['price_percent'];

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
						$sql_isliked = "SELECT * FROM tb_like WHERE iduser = '$user_id' AND idproduct = '$id'";
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