<?php
include( 'vendor/autoload.php' );	//Подключаем библиотеку
include( 'AvailableSession.php' );	//Подключаем рассписание университета
include( 'Link.php' ); 				//Подключаем линки для бмв

use Telegram\Bot\Api;

$telegram = new Api( '420523762:AAFPPwjtM-azuyylScQ7SYruZVta_tGC1kM' ); //Устанавливаем токен, полученный у BotFather
$result   = $telegram->getWebhookUpdate(); //Передаем в переменную $result полную информацию о сообщении пользователя

$text    = $result["message"]["text"]; //Текст сообщения
$chat_id = $result["message"]["chat"]["id"]; //Уникальный идентификатор чата
$name    = $result["message"]["from"]["username"]; //Юзернейм пользователя

$explodeText = explode(' ', $text);

if ( $text ) {
	if ( $text == "/hate" ) {
		$garusHate = [
			'просто привет Гарис'
		];
		$reply     = getRandValue( $garusHate );
		$telegram->sendMessage( [ 'chat_id' => $chat_id, 'text' => $reply ] );
	} elseif ( $text == "/сегодня" ) {
		$uniTable = new AvailableSession();
		$today_events = $uniTable->getReadableTodayEvents();
		if ( is_array( $today_events ) ) {
			$reply = $uniTable->getStringTodayEvents($today_events);
			$telegram->sendMessage( [ 'chat_id' => $chat_id, 'text' => $reply ] );
		} else if ( is_string( $today_events ) ) {
			$telegram->sendMessage( [ 'chat_id' => $chat_id, 'text' => $today_events ] );
		}
	} else if ($explodeText[0] === '/bmw_e30'){

		$link = new Link();

		if(count($explodeText) === 2){
			$result = $link->getLinks($text);
		} elseif (count($explodeText) === 3){
			$result = $link->setLink($text);
		}

		$telegram->sendMessage( [ 'chat_id' => $chat_id, 'text' => $result ] );
	}
}



function getRandValue( $array ) {
	$lenth     = count( $array );
	$randValue = $array[ rand( 0, $lenth ) ];

	return $randValue;
}
