<?php
function getUserByToken($token)
{
		// get seller id by token
	$decode_token = base64_decode($token);
	$pieces_token = explode("_", $decode_token);
	$user_id = $pieces_token[0];
	if (empty($user_id)) {
		$user_id = "0";
	}
	return $user_id;
}
function getTimeInString() {
	date_default_timezone_set("Asia/Bangkok");
	$date = date('Y-m-d H:i:s');
	return $date;
}
function deleteFile($path, $domain) {
	$len = strlen($domain);
	$path = substr($path, $len);
	$path = ".".$path;
	if (file_exists($path))
	{
		unlink($path);
	}
}
function check($category) {
	for ($i=0; $i < count($category); $i++) { 
		if ($category[$i]['check'] == 0) {
			return $i;
		}
	}
	if ($i == count($category)) {
		return -1;
	}
}

function sendCloudMessaseToAndroid($deviceToken, $message) {        
    $url = 'https://fcm.googleapis.com/fcm/send ';
    $serverKey = "*** Key mà ở trên mình đã bảo bạn copy ra ý ***";
    $msg = $message;  
    $fields = array();
    $fields['message'] = $msg;
    if (is_array($deviceToken)) {
        $fields['registration_ids'] = $deviceToken;
    } else {
        $fields['to'] = $deviceToken;
    }
    $headers = array(
        'Content-Type:application/json',
        'Authorization:key=' . $serverKey
    );   
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);
    if ($result === FALSE) {
        die('FCM Send Error: '  .  curl_error($ch));
    }
    curl_close($ch);
    return $result;
}   

?>