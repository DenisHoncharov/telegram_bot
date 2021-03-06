<?php
include('vendor/autoload.php');     //Подключаем библиотеку
include('AvailableSession.php');    //Подключаем рассписание университета
include('Link.php');                //Подключаем линки для бмв
include('MyCustomException.php');	//Подключаем кастомные ексепшены

use Telegram\Bot\Api;

$telegram = new Api('420523762:AAFPPwjtM-azuyylScQ7SYruZVta_tGC1kM'); //Устанавливаем токен, полученный у BotFather
$result = $telegram->getWebhookUpdate(); //Передаем в переменную $result полную информацию о сообщении пользователя

error_log('Request with start');
error_log(json_encode($result, JSON_PRETTY_PRINT));

if ($result['message']['entities']) {

    error_log('Request with entities');
    error_log(json_encode($result, JSON_PRETTY_PRINT));

	$text    = $result["message"]["text"];             //Текст сообщения
	$chat_id = $result["message"]["chat"]["id"];    //Уникальный идентификатор чата
	$user_id = $result["message"]["from"]["id"];    //Уникальный идентификатор чата
	$name    = $result["message"]["from"]["username"]; //Юзернейм пользователя

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
			$reply = 	"Этот бот создан для того что-бы помочь новичкам.\n\nУ него есть несколько основных комманд, которых со временем будет становиться больше.\n\nПеречень доступных комманд:\n/bmw_e30 тема_одним_которая_вам_интересна\n/bmw_e30 тема_одним_словом ссылка_на_статью_по_теме\n/bmw_e30 delete номер_записи_для_удаления";
			$requestParams['text'] = $reply;
		} elseif ($text == "/bmw_e30") {
			$reply = 	"Перечень доступных комманд:\n/bmw_e30 тема_одним_которая_вам_интересна\n/bmw_e30 тема_одним_словом ссылка_на_статью_по_теме\n/bmw_e30 delete номер_записи_для_удаления";
			$requestParams['text'] = $reply;
		} elseif ($text == "/hate") {
			$garusHate = [
				'урод Гарис',
				'моральный пидар Гарис',
				'Гарис мандахуй',
				'Гарус - вантус',
				"Я ебал мамку Гариса",
				"Хочу от Гариса детей"
			];
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

		} elseif(substr($text, 0, 4) == '/gif'|| $text === '/boobs' || $text === '/butts' || $text === '/garisaMamka') {

            $gifLink = new Link();

            if($explodeText[0] === '/gif') {
                try {
                    $gifLink = $gifLink->getGifLink($text);

                    $telegram->sendVideo(
                        ['chat_id' => $chat_id, 'video' => $gifLink]
                    );

                    return;
                } catch (MyCustomException $e) {
                    $requestParams['text'] = $e->getMessage();
                }
            }else{

                if($text === '/garisaMamka'){
                    $text = getRandValue(array('/boobs', '/butts'));
                }

                $tag = trim(str_replace('/', '', $text));

                $url = 'http://api.o'. $tag .'.ru/'. $tag .'/1/1/random';

                $api = new APIConnection($url);
                $photoResponse = $api->getResponseResult();

                $photo = json_decode($photoResponse)[0];
                $photo = explode('/', $photo->preview)[1];

                $photoLink = 'http://media.o'. $tag .'.ru/'. $tag .'/' . $photo;

                $telegram->sendPhoto(
                    ['chat_id' => $chat_id, 'photo' => $photoLink]
                );

                return;
            }

        } else {
		    $requestParams['text'] = '';
        }

		error_log('Response:');
		error_log(json_encode($result));

		error_log('Request:');
		error_log($requestParams['text']);

		if ($requestParams['text'] === '') {
            $telegram->sendMessage($requestParams);
        }

	}
}


function getRandValue($array) {
	$lenth = count($array);
	$randValue = $array[rand(0, $lenth)];

	return $randValue;
}
