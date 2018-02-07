<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!isset($_POST['id'])) {
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

				$id = $_POST['id'];

				$sql = "SELECT * FROM products WHERE id = $id";
				$result = mysqli_query($conn, $sql);
				$row = mysqli_fetch_assoc($result);

				$data['id'] = $row['id'];
				$data['name'] = $row['name'];
				$data['price'] = $row['price'];
				$data['price_percent'] = $row['price_percent'];
				$data['described'] = $row['described'];
				$data['ships_from'] = $row['ships_from'];
				$data['ships_from_id'] = explode("-", $row['ships_from_id']);
				$data['condition'] = $row['condition_p'];
				$data['created'] = $row['created'];
				$data['banned'] = $row['banned'];

				// get seller profile
				$seller = array();
				$seller['id'] = $row['seller_id'];
				$seller_id = $row['seller_id'];
				$sql_temp = "SELECT * FROM user WHERE id = $seller_id";
				$result_temp = mysqli_query($conn, $sql_temp);
				$row_temp = mysqli_fetch_assoc($result_temp);
				$seller['username'] = $row_temp['username'];
				$seller['avatar'] = $row_temp['avatar'];
				$seller['score'] = $row_temp['score'];
				//get listing of seller
				$sql_listing_seller = "SELECT COUNT(id) FROM products WHERE seller_id = '$seller_id'";
				$result_listing_seller = mysqli_query($conn,$sql_listing_seller);
				$row_listing_seller = mysqli_fetch_assoc($result_listing_seller);
				$listing = $row_listing_seller['COUNT(id)'];
				$seller['listing'] = $listing;
				$data['seller'] = $seller;

				//get image or video
				$sql_file = "SELECT * FROM file_product WHERE id_product = '$id'";
				$result_file = mysqli_query($conn, $sql_file);
				$row_file = mysqli_fetch_assoc($result_file);
				if ($row_file['type'] == 'image') {
					$image = array();
					$image_element = array();
					$image_element['id'] = $row_file['id'];
					$image_element['url'] = $row_file['url'];
					array_push($image, $image_element);
					while ($row_file = mysqli_fetch_assoc($result_file)) {
						$image_element = array();
						$image_element['id'] = $row_file['id'];
						$image_element['url'] = $row_file['url'];
						array_push($image, $image_element);
					}
					$data['image'] = $image;
				} else {
					$video = array();
					$video['url'] = $row_file['url'];
					$video['thumb'] = $row_file['thumbnails'];
					$data['video'] = $video;
				}

				// get can edit by comparing between id user in token and id seller
				$user_id = getUserByToken($token);
				if ($seller_id == $user_id) {
					$can_edit = "1"; 
				} else {
					$can_edit = "0";
				}
				$data['can_edit'] = $can_edit;

				// get brand by using brand_id
				$brand_id = $row['brand_id'];
				$sql_brand = "SELECT * FROM brand WHERE id = '$brand_id'";
				$result_brand = mysqli_query($conn, $sql_brand);
				$row_brand = mysqli_fetch_assoc($result_brand);
				if ($row_brand != NULL) {
					$brand = array();
					$brand['id'] = $brand_id;
					$brand['brand_name'] = $row_brand['name'];
					$data['brand'] = $brand;
				}

				// get size by using size id
				$size_id = $row['size_id'];
				$sql_size = "SELECT * FROM size WHERE id = '$size_id'";
				$result_size = mysqli_query($conn, $sql_size);
				$row_size = mysqli_fetch_assoc($result_size);
				if ($row_size != NULL) {
					$size = array();
					$size['id'] = $size_id;
					$size['size_name'] = $row_size['name'];
					$data['size'] = $size;
				}

				// get category by using category id
				$category_id = $row['category_id'];
				$category_id_temp = $category_id;
				$category = array();
				while ($category_id_temp != 0) {
					$sql_category = "SELECT * FROM category WHERE id = '$category_id_temp'";
					$result_category = mysqli_query($conn, $sql_category);
					$row_category = mysqli_fetch_assoc($result_category);
					$category_element = array();
					$category_element['id'] = $category_id;
					$category_element['name'] = $row_category['name'];
					$category_element['has_brand'] = $row_category['has_brand'];
					$category_element['has_size'] = $row_category['has_size'];
					$category_element['require_weight'] = $row_category['require_weight'];
					$category_element['has_child'] = $row_category['has_child'];
					array_push($category, $category_element);
					$category_id_temp = $row_category['parent_id'];
				}
				$data['category'] = $category;

				//get islike
				$sql_isliked = "SELECT * FROM tb_like WHERE iduser = '$user_id' AND idproduct = '$id'";
				$result_islike = mysqli_query($conn, $sql_isliked);
				if (mysqli_num_rows($result_islike) == 0) {
					$islike = "0";
				} else {
					$islike = "1";
				}
				$data['is_liked'] = $islike;

				// get number of like of product
				$sql_like = "SELECT COUNT(iduser) FROM tb_like WHERE idproduct = '$id'";
				$result_like = mysqli_query($conn, $sql_like);
				$row_like = mysqli_fetch_assoc($result_like);
				$like = $row_like['COUNT(iduser)'];
				$data['like'] = $like;

				// get number of comment of product
				$sql_cmt = "SELECT COUNT(poster_id) FROM comment WHERE product_id = '$id'";
				$result_cmt = mysqli_query($conn, $sql_cmt);
				$row_cmt = mysqli_fetch_assoc($result_cmt);
				$cmt = $row_cmt['COUNT(poster_id)'];
				$data['comment'] = $cmt;

				// get is_blocked
				$sql_isblocked = "SELECT * FROM relative WHERE own_id = '$seller_id' AND object_id = '$user_id' AND type = 'block'";
				$result_isblocked = mysqli_query($conn, $sql_isblocked);
				if (mysqli_num_rows($result_isblocked) == 0) {
					$isblocked = "0";
				} else {
					$isblocked = "1";
				}
				$data['is_blocked'] = $isblocked;

				$data['url'] = $row['url'];

				if (!empty($row['weight'])) {
					$data['weight'] = $row['weight'];
				}
				if (!empty($row['dimension'])) {
					$dimension = explode("x", $row['dimension']);
					$data['dimension'] = $dimension;
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