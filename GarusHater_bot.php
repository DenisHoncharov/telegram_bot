<?php
include( 'vendor/autoload.php' ); //Подключаем библиотеку
use Telegram\Bot\Api;

$telegram = new Api( '420523762:AAFPPwjtM-azuyylScQ7SYruZVta_tGC1kM' ); //Устанавливаем токен, полученный у BotFather
$result   = $telegram->getWebhookUpdate(); //Передаем в переменную $result полную информацию о сообщении пользователя

$text     = $result["message"]["text"]; //Текст сообщения
$chat_id  = $result["message"]["chat"]["id"]; //Уникальный идентификатор чата
$name     = $result["message"]["from"]["username"]; //Юзернейм пользователя
$user_id  = $result["message"]["from"]["id"];

$keyboard = [ [ "Последние статьи" ], [ "Картинка" ], [ "Гифка" ] ]; //Клавиатура

if ( $text ) {
	$reply = json_encode($result);
	$telegram->sendMessage( [ 'chat_id' => $chat_id, 'text' => $reply ] );
	if ( $text == "/start" ) {
		$reply        = "Добро пожаловать в бота!";
		$reply_markup = $telegram->replyKeyboardMarkup( [
			'keyboard'          => $keyboard,
			'resize_keyboard'   => true,
			'one_time_keyboard' => false
		] );
		$telegram->sendMessage( [ 'chat_id' => $chat_id, 'text' => $reply, 'reply_markup' => $reply_markup ] );
	} elseif ( $text == "/hate" ) {
		$garusHate = [
			"Гарус иди на хуй!",
			'Ебал мамку Гаруса',
			"Гарус уебанус",
			"Гарус пиздюк",
			"Гарус сосет хуй",
			"Гариса мамка Саша Грей"
		];
		$reply     = getRandValue( $garusHate );
		$telegram->sendMessage( [ 'chat_id' => $chat_id, 'text' => $reply ] );
	} elseif ( $user_id == 254346170 ) {
		$reply     = 'Блять, заебал, не пиши сюда';
		$telegram->sendMessage( [ 'chat_id' => $chat_id, 'text' => $reply ] );
	} else {
		$reply = "По запросу \"<b>" . $text . "</b>\" ничего не найдено.";
		$telegram->sendMessage( [ 'chat_id' => $chat_id, 'parse_mode' => 'HTML', 'text' => $reply ] );
	}
} else {
	$telegram->sendMessage( [ 'chat_id' => $chat_id, 'text' => "Отправьте текстовое сообщение." ] );
}

function getRandValue( $array ) {
	$lenth     = count( $array );
	$randValue = $array[ rand( 0, $lenth ) ];

	return $randValue;
}

?>