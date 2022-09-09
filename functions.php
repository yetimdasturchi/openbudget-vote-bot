<?php

function setUserConfig($chat_id='', $key='', $value='') {
	$file = 'users/'.$chat_id.'.json';
    if (file_exists( $file )) {
		$user_data = file_get_contents( $file );
		$user_data = json_decode( $user_data, TRUE );
	}else{
		$user_data = [];
	}
	$user_data[$key] = $value; 
	write_file( $file, json_encode( $user_data ) );

	return TRUE;
}

function getUserConfig($chat_id='', $key='') {
	$file = 'users/'.$chat_id.'.json';
	if (file_exists( $file )) {
		$user_data = file_get_contents( $file );
		$user_data = json_decode( $user_data, TRUE );
	}else{
        $user_data = [];
    }

	if (array_key_exists($key, $user_data)) {
        return $user_data[$key];
    }

	return FALSE;
}

function write_file( $path, $data, $mode = 'wb') {
	if ( ! $fp = @fopen( $path, $mode ) ) return FALSE;

	flock( $fp, LOCK_EX );

	for ( $result = $written = 0, $length = strlen( $data ); $written < $length; $written += $result ) {
		if ( ( $result = fwrite( $fp, substr( $data, $written ) ) ) === FALSE ) break;
	}

	flock( $fp, LOCK_UN );
	fclose( $fp );

	return is_int( $result );
}

function getPagination( $query, $current, $maxpage, $type ) {
    $q = $query;
    $keys = [];
    if ($current>0) $keys[] = ['text' => "◀️ Avvalgi", 'callback_data' => http_build_query([$type => $q, 'prev' => strval(($current-1))])];
    if ($current != $maxpage-1)  $keys[] = ['text' => "Keyingi ▶️", 'callback_data' => http_build_query([$type => $q, 'next' => strval(($current+1))])];
    //if ($current<$maxpage) $keys[] = ['text' => strval($maxpage).'»', 'callback_data' => strval($maxpage)];
	return [$keys];
}

function user($user, $users_count) {
	$message = "";
	foreach($user as $k => $v){ $user[$k] = htmlentities($v);};
	if ( !empty( $user['id'] ) ) {
    	$message .= "🆔 <a href=\"tg://user?id={$user['id']}\">{$user['id']}</a>".PHP_EOL;
    	$message .= str_repeat("-", 40).PHP_EOL;
    }
	if ( !empty( $user['first_name'] ) ) {
    	$message .= "▫️ {$user['first_name']}".PHP_EOL;   
	}
    if ( !empty( $user['last_name'] ) ) {
    	$message .= "▫️ {$user['last_name']}".PHP_EOL;
    }
    if ( !empty( $user['username'] ) ) {
    	$message .= "▫️ @{$user['username']}".PHP_EOL;
    }
    if ( !empty( $user['lastmessage'] ) || !empty( $user['lastaction'] ) ) {
    	$message .= PHP_EOL.str_repeat("-", 40).PHP_EOL;
    }
    if ( !empty( $user['lastmessage'] ) ) {
    	$message .= "💬 {$user['lastmessage']}".PHP_EOL;
	}
    if ( !empty( $user['lastsection'] ) ) {
        $message .= "🔌 {$user['lastsection']}".PHP_EOL;
        }

    if ( !empty( $user['lastaction'] ) ) {
    	$lastaction = date("Y-m-d | H:i:s", $user['lastaction']);
        $message .= "🕐 {$lastaction}".PHP_EOL;
	}

	$uc = getUserConfig( $user['id'], 'balance');
	if (empty($uc)) $uc = "0";
    $message .= "💰 ".$uc.PHP_EOL;

    $votes = getUserConfig( $user['id'], 'votes');
	if (empty($votes)) $votes = "0";
    $message .= "🗣 ".$votes.PHP_EOL;

    $ref = getUserConfig( $user['id'], 'referals');
	if (empty($ref)) $ref = "0";

	$message .= "🔗 ".$ref.PHP_EOL;

	$message .= str_repeat("-", 40);
	$message .= PHP_EOL."👥 Jami:" . " {$users_count}";
	error_log($message);
	return $message;
}

function owner($user, $users_count) {
	$message = "";
	foreach($user as $k => $v){ $user[$k] = htmlentities($v);};
	if ( !empty( $user['id'] ) ) {
    	$message .= "🆔 <a href=\"tg://user?id={$user['id']}\">{$user['id']}</a>".PHP_EOL;
    	$message .= str_repeat("-", 40).PHP_EOL;
    }
	if ( !empty( $user['first_name'] ) ) {
    	$message .= "▫️ {$user['first_name']}".PHP_EOL;   
	}
    if ( !empty( $user['last_name'] ) ) {
    	$message .= "▫️ {$user['last_name']}".PHP_EOL;
    }
    if ( !empty( $user['username'] ) ) {
    	$message .= "▫️ @{$user['username']}".PHP_EOL;
    }

	$message .= str_repeat("-", 40);
	$message .= PHP_EOL."👥 Jami:" . " {$users_count}";
	error_log($message);
	return $message;
}

function get_users($all=FALSE) {
    global $owners;
    $users = glob('users/*.json');
    $temp_users = [];
    foreach ($users as $user) {
        $fileName = basename($user);
        $chat_id = str_replace('.json', '', $fileName);
        if (!$all) {
        	if ( in_array( $chat_id, $owners ) ) continue;
        }
        if(file_exists($user)){
            $user = file_get_contents($user);
            $user = json_decode($user, TRUE);
            $user['id'] = $chat_id;
            $temp_users[] = $user;
            
        }
    }

    usort($temp_users, function( $a, $b ) {
    	if(empty($a['lastaction'])) $a['lastaction'] = 0;
    	if(empty($b['lastaction'])) $b['lastaction'] = 0;
    	return $b['lastaction'] <=> $a['lastaction'];
	});
    
    return $temp_users;
}

function get_votes(){
	global $config;
    $votes = glob('votes/*.json');
    $temp_votes = [];
    foreach ($votes as $vote) {
        if(file_exists($vote)){
            $filename = $vote;
            $vote = file_get_contents($vote);
            $vote = json_decode($vote, TRUE);
            $vote['filename'] = $filename;
            $temp_votes[] = $vote; 
        }
    }

    usort($temp_votes, function( $a, $b ) {
    	if(empty($a['time'])) $a['time'] = 0;
    	if(empty($b['time'])) $b['time'] = 0;
    	return $b['time'] <=> $a['time'];
	});
    
    return $temp_votes;
}

function vote($vote, $applications_count) {
	$users = get_users(TRUE);
	$user = [];
	foreach ($users as $u) {
		if ($u['id'] == $vote['chat_id']) {
			$user = $u;
			break;	
		}
	}

	$message = "";
	if ( !empty( $user['id'] ) ) {
    	$message .=  "🆔 <a href=\"tg://user?id={$user['id']}\">{$user['id']}</a>".PHP_EOL;
    	$message .= str_repeat("-", 40).PHP_EOL;
    }
	if ( !empty( $user['first_name'] ) ) {
    	$message .= "▫️ {$user['first_name']}".PHP_EOL;   
	}
    if ( !empty( $user['last_name'] ) ) {
    	$message .= "▫️ {$user['last_name']}".PHP_EOL;
    }
    if ( !empty( $user['username'] ) ) {
    	$message .= "▫️ @{$user['username']}".PHP_EOL;
    }

    $message .= str_repeat("-", 40).PHP_EOL;
    $message .= "🕐 ".date("Y-m-d | H:i:s", $vote['time']).PHP_EOL;
    $message .= str_repeat("-", 40).PHP_EOL;
    
    $message .= "📞 ".format_phone($vote['phone']).PHP_EOL;

    $uc = getUserConfig( $user['id'], 'balance');
	if (empty($uc)) $uc = "0";
    $message .= "💰 ".$uc.PHP_EOL;

    $votes = getUserConfig( $user['id'], 'votes');
	if (empty($votes)) $votes = "0";
    $message .= "🗣 ".$votes.PHP_EOL;

	$message .= str_repeat("-", 40);
	$message .= PHP_EOL. "📝 Jami: {$applications_count}";
	return $message;
}



function add_vote($data=[]) {
	global $config, $tg;
	$fileName = 'votes/' . md5( generate_uuid() . time() ).'.json';
	if (!file_exists($fileName)) {
		file_put_contents($fileName, json_encode($data));
		return TRUE;
	}

	return FALSE;
	
}

function check_phonenumber($phone){
	global $config;
    $votes = glob('votes/*.json');
    $temp_votes = [];
    foreach ($votes as $vote) {
        if(file_exists($vote)){
            $filename = $vote;
            $vote = file_get_contents($vote);
            $vote = json_decode($vote, TRUE);
            if ($vote['phone'] == $phone) {
            	return TRUE;
            }
        }
    }
    return FALSE;
}

function get_applications($del=FALSE) {
	global $config;
    $apps = glob('requests/*.json');
    $temp_applications = [];
    foreach ($apps as $app) {
        if(file_exists($app)){
            $filename = $app;
            $app = file_get_contents($app);
            $app = json_decode($app, TRUE);
            $app['filename'] = $filename;
            if($del){
        		if (intval($app['time']) == intval($del)) {
        			@unlink($filename);
        		}
        	}else{
        		$temp_applications[] = $app;
        	}    
        }
    }

    usort($temp_applications, function( $a, $b ) {
    	if(empty($a['time'])) $a['time'] = 0;
    	if(empty($b['time'])) $b['time'] = 0;
    	return $b['time'] <=> $a['time'];
	});
    
    return $temp_applications;
}
function application($application, $applications_count) {
	$users = get_users(TRUE);
	$user = [];
	foreach ($users as $u) {
		if ($u['id'] == $application['chat_id']) {
			$user = $u;
			break;	
		}
	}

	$message = "";
	if ( !empty( $user['id'] ) ) {
    	$message .=  "🆔 <a href=\"tg://user?id={$user['id']}\">{$user['id']}</a>".PHP_EOL;
    	$message .= str_repeat("-", 40).PHP_EOL;
    }
	if ( !empty( $user['first_name'] ) ) {
    	$message .= "▫️ {$user['first_name']}".PHP_EOL;   
	}
    if ( !empty( $user['last_name'] ) ) {
    	$message .= "▫️ {$user['last_name']}".PHP_EOL;
    }
    if ( !empty( $user['username'] ) ) {
    	$message .= "▫️ @{$user['username']}".PHP_EOL;
    }

    $message .= str_repeat("-", 40).PHP_EOL;
    $message .= "🕐 ".date("Y-m-d | H:i:s", $application['time']).PHP_EOL;
    $message .= str_repeat("-", 40).PHP_EOL;
    
    $message .= "🆔 ".$application['text'].PHP_EOL;

    $uc = getUserConfig( $user['id'], 'balance');
	if (empty($uc)) $uc = "0";
    $message .= "💰 ".$uc.PHP_EOL;

	$message .= str_repeat("-", 40);
	$message .= PHP_EOL. "📝 Jami: {$applications_count}";
	return $message;
}

function addRequest($data=[]) {
	global $config, $tg;
	$fileName = 'requests/' . $data['chat_id'].'.json';
	if (!file_exists($fileName)) {
		file_put_contents($fileName, json_encode($data));
		return TRUE;
	}

	return FALSE;
	
}

function generate_uuid() {
    return sprintf( '%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

function clear_phone( $number ) {
  return preg_replace( '/\D/', '',  $number );
}

function validate_phone( $number ) {
  return  boolval( preg_match( "/^998(90|91|93|94|95|97|98|99|33|88)[0-9]{7}$/", $number ) );
}

function format_phone( $number ) {
    return preg_replace( '/^(998)(90|91|93|94|95|97|98|99|33|88)([0-9]{3})([0-9]{2})([0-9]{2})$/', '+$1 ($2) $3-$4-$5', $number );
}

function api($method, $data){
	
	$ch = curl_init( 'opb.php?method=' . $method );
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_TIMEOUT, 2);
	curl_setopt( $ch, CURLOPT_HTTPHEADER, array("REMOTE_ADDR: 37.110.212.137", "HTTP_X_FORWARDED_FOR: 37.110.212.137", "HTTP_X_REAL_IP: 37.110.212.137"));

	$response = curl_exec($ch);

	curl_close($ch);

	return json_decode($response, TRUE);
}

function message_status($set=FALSE){
	if ($set == 'count') {
		return count(
			glob("notifications/*.{json}",GLOB_BRACE)
		);
	}

	$status_file = dirname(__FILE__).'/data/status.dat';
	
	if (in_array($set, ['on', 'off'])) {
		file_put_contents($status_file, $set);
		return $set;
	}

	if ( file_exists($status_file) ) {
		return file_get_contents($status_file);
	}

	return 'on';
}

function add_notifications($message) {
	$users = get_users(TRUE);
	$users_count = count( $users );
	if ($users_count == 0) return FALSE;

	foreach ($users as $user) {
		$message['chat_id'] = $user['id'];
    	$id = 'notifications/' . md5( generate_uuid() . time() ).'.json';
		file_put_contents($id, json_encode($message));
		usleep(2);
	}

	return TRUE;
}

function clear_notification(){
	array_map( 'unlink', array_filter((array) glob("notifications/*") ) );
}

function clear_votes(){
	array_map( 'unlink', array_filter((array) glob("votes/*") ) );
}

function get_url() {
	return $_SERVER["REQUEST_SCHEME"] . '://' . $_SERVER['HTTP_HOST'] .  dirname($_SERVER["PHP_SELF"]) . '/';
}

function to_xls($data, $filename){
	$fp = fopen($filename, "w+");
	$str = pack(str_repeat("s", 6), 0x809, 0x8, 0x0, 0x10, 0x0, 0x0); 
	fwrite($fp, $str);
	if (is_array($data) && !empty($data)){
	    $row = 0;
	    foreach (array_values($data) as $_data){
	        if (is_array($_data) && !empty($_data)){
	            if ($row == 0){
	                foreach (array_keys($_data) as $col => $val){
	                    _xlsWriteCell($row, $col, $val, $fp);
	                }
	                $row++;
	            }
	            foreach (array_values($_data) as $col => $val){
	                _xlsWriteCell($row, $col, $val, $fp);
	            }
	            $row++;
	        }
	    }
	}
	$str = pack(str_repeat("s", 2), 0x0A, 0x00);
	fwrite($fp, $str);
	fclose($fp);
}

function _xlsWriteCell($row, $col, $val, $fp){
	if (is_float($val) || is_int($val)){
	    $str  = pack(str_repeat("s", 5), 0x203, 14, $row, $col, 0x0);
	    $str .= pack("d", $val);
	} else {
	    $l    = strlen($val);
	    $str  = pack(str_repeat("s", 6), 0x204, 8 + $l, $row, $col, 0x0, $l);
	    $str .= $val;
	}
	fwrite($fp, $str);
}