<?php
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		if (!isset($_POST['level']) || !isset($_POST['parent_id'])) {
			$code = "1002";
			$message = 'Parameter is not enought.';
		} else {
			require('Config.php');
			if ($conn == null) {
				$code = '1001';
				$message = 'Can not connect to DB.';
			} else {
				$data = array();
				require('utils.php');

				$level = $_POST['level'];
				$parent_id = $_POST['parent_id'];

				if ($level == "0") {
					$sql = "SELECT * FROM province";
					$result = mysqli_query($conn, $sql);
					while ($row = mysqli_fetch_assoc($result)) {
						$data_element = array();
						$data_element['id'] = $row['provinceid'];
						$data_element['name'] = $row['type']." ".$row['name'];
						array_push($data, $data_element);
					}
				} else if ($level == "1") {
					$sql = "SELECT * FROM district WHERE provinceid = '$parent_id'";
					$result = mysqli_query($conn, $sql);
					while ($row = mysqli_fetch_assoc($result)) {
						$data_element = array();
						$data_element['id'] = $row['districtid'];
						$data_element['name'] = $row['type']." ".$row['name'];
						array_push($data, $data_element);
					}
				} else if ($level == "2") {
					$sql = "SELECT * FROM ward WHERE districtid = '$parent_id'";
					$result = mysqli_query($conn, $sql);
					while ($row = mysqli_fetch_assoc($result)) {
						$data_element = array();
						$data_element['id'] = $row['wardid'];
						$data_element['name'] = $row['type']." ".$row['name'];
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