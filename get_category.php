<?php
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		require('Config.php');
		if ($conn == null) {
			$code = '1001';
			$message = 'Can not connect to DB.';
		} else {
			require('utils.php');
			$data = array();

			$parent_id = $_POST['parent_id'];

			$sql = "SELECT * FROM category WHERE parent_id = '$parent_id'";
			$result = mysqli_query($conn, $sql);
			while ($row = mysqli_fetch_assoc($result)) {
				$data_element = array();
				$data_element['id'] = $row['id'];
				$data_element['name'] = $row['name'];
				$data_element['has_brand'] = $row['has_brand'];
				$data_element['has_size'] = $row['has_size'];
				$data_element['has_child'] = $row['has_child'];
				$data_element['require_weight'] = $row['require_weight'];

				array_push($data, $data_element);
			}
			$code = '1000';
			$message = 'OK';
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