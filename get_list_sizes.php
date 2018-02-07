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
				$sql = "SELECT * FROM size";
				$result = mysqli_query($conn, $sql);
				while ($row = mysqli_fetch_assoc($result)) {
					$data_element = array();
					$data_element['id'] = $row['id'];
					$data_element['size_name'] = $row['name'];
					array_push($data, $data_element);
				}
			} else {
				$sql = "SELECT size FROM category WHERE id = '$category_id'";
				$result = mysqli_query($conn, $sql);
				$row = mysqli_fetch_assoc($result);
				$arr_row = explode("_", $row['size']);
				for ($i=0; $i < count($arr_row); $i++) { 
					$id_size = $arr_row[$i];
					$data_element = array();
					$sql_size = "SELECT * FROM size WHERE id = '$id_size'";
					$result_size = mysqli_query($conn, $sql_size);
					$row_size = mysqli_fetch_assoc($result_size);
					$data_element['id'] = $row_size['id'];
					$data_element['size_name'] = $row_size['name'];
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