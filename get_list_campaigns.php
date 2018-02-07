<?php
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		require('Config.php');
		if ($conn == null) {
			$code = '1001';
			$message = 'Can not connect to DB.';
		} else {
			$data = array();
			$sql = "SELECT * FROM campaign";
			$result = mysqli_query($conn, $sql);

			while ($row = mysqli_fetch_assoc($result)) {
				$data_element = array();
				$data_element['id'] = $row['id'];
				$data_element['name'] = $row['name'];
				$data_element['url'] = $row['url'];
				$data_element['banned'] = $row['banned'];
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