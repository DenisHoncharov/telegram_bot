<?php
include('vendor/autoload.php');        //Подключаем библиотеку
include('AvailableSession.php');    //Подключаем рассписание университета
include('Link.php');                //Подключаем линки для бмв

use Telegram\Bot\Api;

$telegram = new Api('420523762:AAFPPwjtM-azuyylScQ7SYruZVta_tGC1kM'); //Устанавливаем токен, полученный у BotFather
$result = $telegram->getWebhookUpdate(); //Передаем в переменную $result полную информацию о сообщении пользователя

if ($result['message']['entities']) {

	$text = $result["message"]["text"];            //Текст сообщения
	$chat_id = $result["message"]["chat"]["id"];    //Уникальный идентификатор чата
	$user_id = $result["message"]["from"]["id"];    //Уникальный идентификатор чата
	$name = $result["message"]["from"]["username"]; //Юзернейм пользователя

	if ($text) {

		$explodeText = explode(' ', $text);

		$reply = '';
		$requestParams =
			array('chat_id' => $chat_id, 'text' => '', 'parse_mode' => '', 'disable_web_page_preview' => '',
				  'disable_notification' => '', 'reply_to_message_id' => '', 'reply_markup' => '');

		if ($text == "/start") {
			$reply = "Добро пожаловать в бота BMW помощника!";
			$requestParams['text'] = $reply;
		} elseif ($text == "/help") {
			$reply = 	"Этот бот создан для того что-бы помочь новичкам.\nУ него есть несколько основных комманд, которых со временем будет становиться больше.\nПеречень доступных комманд:\n/bmw_e30 тема_одним_которая_вам_интересна\n/bmw_e30 тема_одним_словом ссылка_на_статью_по_теме\n/bmw_e30 delete номер_записи_для_удаления";
			$requestParams['text'] = $reply;
		} elseif ($text == "/hate") {
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

			if ($explodeText[1] == 'delete') {
				$reply = $link->deleteLinks($text);
			} elseif (count($explodeText) === 2) {
				$reply = $link->getLinks($text);
				$reply = $link->showLinks($reply);
				$requestParams['disable_web_page_preview'] = true;
				$requestParams['parse_mode'] = 'HTML';
			} elseif (count($explodeText) === 3) {
				$reply = $link->setLink($text);
			}

			$requestParams['text'] = $reply;
		}

		error_log('Response:');
		error_log(json_encode($result));

		error_log('Request:');
		error_log($requestParams['text']);

		$telegram->sendMessage($requestParams);

	}
}


function getRandValue($array) {
	$lenth = count($array);
	$randValue = $array[rand(0, $lenth)];

	return $randValue;
}
