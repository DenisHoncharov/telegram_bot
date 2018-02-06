<?php
include('vendor/autoload.php');    //Подключаем библиотеку
include('AvailableSession.php');    //Подключаем рассписание университета
include('Link.php');                //Подключаем линки для бмв

use Telegram\Bot\Api;

$telegram = new Api('420523762:AAFPPwjtM-azuyylScQ7SYruZVta_tGC1kM'); //Устанавливаем токен, полученный у BotFather
$result = $telegram->getWebhookUpdate(); //Передаем в переменную $result полную информацию о сообщении пользователя

$text = $result["message"]["text"]; //Текст сообщения
$chat_id = $result["message"]["chat"]["id"]; //Уникальный идентификатор чата
$name = $result["message"]["from"]["username"]; //Юзернейм пользователя

$explodeText = explode(' ', $text);

if ($text) {

	$reply = '';
	$requestParams = array(
		'chat_id' => $chat_id,
		'text' => '',
		'parse_mode' => '',
		'disable_web_page_preview' => '',
		'disable_notification' => '',
		'reply_to_message_id' => '',
		'reply_markup' => ''
	);

	if ($text == "/hate") {
		$garusHate = ['просто привет Гарис'];
		$reply = getRandValue($garusHate);

		$requestParams['text'] = $reply;

	} elseif ($text == "/сегодня") {
		$uniTable = new AvailableSession();
		$today_events = $uniTable->getReadableTodayEvents();
		if (is_array($today_events)) {
			$reply = $uniTable->getStringTodayEvents($today_events);
		}

		$requestParams['text'] = $reply;

	} else if ($explodeText[0] === '/bmw_e30') {

		$link = new Link();

		if (count($explodeText) === 2) {
			$reply = $link->getLinks($text);
			$reply = $link->showLinks($reply);
		} elseif (count($explodeText) === 3) {
			$reply = $link->setLink($text);
			$requestParams['disable_web_page_preview'] = true;
			$requestParams['parse_mode'] = 'HTML';
		}

		$requestParams['text'] = $reply;
	}

	error_log('Response:');
	error_log(json_encode($result));

	error_log('Request:');
	error_log($requestParams['text']);

	$telegram->sendMessage($requestParams);

}


function getRandValue($array) {
	$lenth = count($array);
	$randValue = $array[rand(0, $lenth)];

	return $randValue;
}
