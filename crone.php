<?php

include 'Telegram.php';
include 'functions.php';

$tg = new Telegram([
	'token' => ""
]);


function scan_index($rootDir, $allData=[]) {
	$invisibleFileNames = [".", "..", ".htaccess", ".htpasswd", "errors", "index.html"];
	$dirContent = array_diff(scandir($rootDir), ['..', '.']);
	foreach($dirContent as $key => $content) {
		$path = $rootDir.$content;
		if ( preg_match('/(.*).json/', $content)) {
	    	$allData[$path] = filemtime($path);
		}
	}
	    
	asort($allData);
	return $allData;
}

function send_notifications() {
	global $tg;
	if (message_status() == 'off') return;
	
	$message = scan_index(dirname(__FILE__) . '/notifications/');
	if ( !empty( $message )) {
		$x = 0;
		foreach ($message as $k => $v) {
			if (message_status() == 'off') return;
			if($x == 5){
				sleep(1);
	            $x = 0;
			}
			$item = file_get_contents($k);
			$item = json_decode($item, TRUE);
			$chat_id = $item['chat_id'];

			$tg->set_chatId( $chat_id );
			if ( !empty( $item['text'] ) ) {
				$res = $tg->send_chatAction('typing')->send_message( $item['text'] )->result();
			}
			if ( !empty( $item['photo'] ) ) {
				$caption = (!empty( $item['caption'])) ? $item['caption'] : '';
				$res = $tg->send_chatAction('upload_photo')->send_photo($item['photo'], $caption)->result();
			}

			if ( !empty( $item['video'] ) ) {
				$caption = (!empty( $item['caption'])) ? $item['caption'] : '';
				$res = $tg->send_chatAction('upload_video')->send_video($item['video'], $caption)->result();
			}

			if ( !empty( $item['from_chat_id'] ) ) {
				$res = $tg->request('forwardMessage', [
					'chat_id' => $chat_id,
                    'from_chat_id' => $item['from_chat_id'],
                    'message_id' => $item['message_id']
				]);
			}
			if (!empty($res['ok']) && !empty($res['description'])) {
				if($res['description'] == "Forbidden: bot was blocked by the user"){
					//@unlink( dirname(__FILE__) . '/data/users/'.$chat_id.'.json' );
				}else if(intval($res['error_code']) == 429){
                	sleep(intval($res['parameters']['retry_after']));
					return;
				}
			}

			@unlink($k);
			$x++;
		}
	}
}
while (1){
	send_notifications();
	sleep(1);
}