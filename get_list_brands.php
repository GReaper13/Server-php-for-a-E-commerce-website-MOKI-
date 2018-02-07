<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!isset($_POST['category_id'])) {
		$code = '1002';
		$message = 'Parameter is not enought.';
	} else {
		require('Config.php');
		if ($conn == null) {
			$code = '1001';
			$message = 'Can not connect to DB.';
		} else {
			$data = array();
			require('utils.php');

			$category_id = $_POST['category_id'];
			if ($category_id == '0') {
				$sql = "SELECT * FROM brand";
				$result = mysqli_query($conn, $sql);
				while ($row = mysqli_fetch_assoc($result)) {
					$data_element = array();
					$data_element['id'] = $row['id'];
					$data_element['brand_name'] = $row['name'];
					array_push($data, $data_element);
				}
			} else {
				$sql = "SELECT brand FROM category WHERE id = '$category_id'";
				$result = mysqli_query($conn, $sql);
				$row = mysqli_fetch_assoc($result);
				$arr_row = explode("_", $row['brand']);
				for ($i=0; $i < count($arr_row); $i++) { 
					$id_brand = $arr_row[$i];
					$data_element = array();
					$sql_brand = "SELECT * FROM brand WHERE id = '$id_brand'";
					$result_brand = mysqli_query($conn, $sql_brand);
					$row_brand = mysqli_fetch_assoc($result_brand);
					$data_element['id'] = $row_brand['id'];
					$data_element['brand_name'] = $row_brand['name'];
					array_push($data, $data_element);
				}
			}

			$code = "1000";
			$message = "OK";
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