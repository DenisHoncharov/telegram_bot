<?php
include_once( "telegramDebugger-master/debug.inc" ); //для дебага

include( 'vendor/autoload.php' ); //Подключаем библиотеку
use Telegram\Bot\Api;

$telegram = new Api( '420523762:AAFPPwjtM-azuyylScQ7SYruZVta_tGC1kM' ); //Устанавливаем токен, полученный у BotFather
$result   = $telegram->getWebhookUpdate(); //Передаем в переменную $result полную информацию о сообщении пользователя

$text    = $result["message"]["text"]; //Текст сообщения
$chat_id = $result["message"]["chat"]["id"]; //Уникальный идентификатор чата
$name    = $result["message"]["from"]["username"]; //Юзернейм пользователя
$user_id = $result["message"]["from"]["id"];

if ( $text ) {
	if ( $text == "/hate" ) {
		$garusHate = [
			'просто пивет Гарис'
		];
		$reply     = getRandValue( $garusHate );
		$telegram->sendMessage( [ 'chat_id' => $chat_id, 'text' => $reply ] );
	} elseif ( $text == "/сегодня" ) {
		$today_events = getReadableTodayEvents();
		if ( is_array( $today_events ) ) {
			$reply = getStringTodayEvents($today_events);
			$telegram->sendMessage( [ 'chat_id' => $chat_id, 'text' => $reply ] );
		} else if ( is_string( $today_events ) ) {
			$telegram->sendMessage( [ 'chat_id' => $chat_id, 'text' => $today_events ] );
		}
	}
}

function getRandValue( $array ) {
	$lenth     = count( $array );
	$randValue = $array[ rand( 0, $lenth ) ];

	return $randValue;
}

function getReadableTodayEvents() {
	$timeTableData = getAllEvents();    //get all events

	$events = $timeTableData->events;

	$today_events = getTodayEvents( $events );    //get today events

	$today_events = getComplitedEvents( $today_events, $timeTableData );  //make readable Events

	if ( ! $today_events ) {
		return "Сегодня пар нет";
	}

	return $today_events;
}

function getAllEvents() {
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_URL, "http://cist.nure.ua/ias/app/tt/P_API_EVEN_JSON?timetable_id=4801902" );
	curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "GET" );

	$timeTableData = json_decode( iconv( 'CP1251', 'UTF-8', curl_exec( $ch ) ) );

	curl_close( $ch );

	return $timeTableData;
}

function getTodayEvents( $events ) {   //founds Events for today
	$today_time    = mktime( 0, 0, 0);  //today start time
	$tomorrow_time = $today_time + 3600 * 24;   //end of the day time

	$today_events = []; //today events

	foreach ( $events as $event ) {
		if ( $event->start_time > $today_time && $event->end_time < $tomorrow_time ) {
			$today_events[] = $event;   //add events that are today
		}
	}

	return $today_events;   //return today events
}

function getComplitedEvents( $todayEvents, $timeTableData ) {    //add teachers, subject and type to events

	if ( ! $todayEvents ) {  //looking for empty events
		return false;
	}

	$teachers = $timeTableData->teachers;
	$subjects = $timeTableData->subjects;
	$types    = $timeTableData->types;

	$todayEvents = addTeachersToEvents( $todayEvents, $teachers );    //add teachers
	$todayEvents = addSubjectToEvents( $todayEvents, $subjects );     //add subjects
	$todayEvents = addTypeToEvents( $todayEvents, $types );           //add type
	$todayEvents = addReadebleTime( $todayEvents );                   //add readable time

	return $todayEvents;
}

function addTeachersToEvents( $todayEvents, $teachers ) {  //add teachers to events
	foreach ( $todayEvents as $event ) {
		foreach ( $teachers as $teacher ) {
			if ( isset( $event->teachers[0] ) ) {            //look for empty teachers
				if ( $event->teachers[0] == $teacher->id ) {
					$event->teachers = $teacher->short_name;    //add teacher name to event
				}
			}
		}
	}

	return $todayEvents; //return event with teachers
}

function addSubjectToEvents( $todayEvents, $subjects ) {   //add subjects to events
	foreach ( $todayEvents as $event ) {
		foreach ( $subjects as $subject ) {
			if ( isset( $event->subject_id ) ) {            //look for empty subjects
				if ( $event->subject_id == $subject->id ) {
					$event->subject_id = $subject->title;    //add subject title to event
				}
			}
		}
	}

	return $todayEvents; //return event with subjects
}

function addTypeToEvents( $todayEvents, $types ) {   //add types to events
	foreach ( $todayEvents as $event ) {
		foreach ( $types as $type ) {
			if ( isset( $event->type ) ) {            //look for empty types
				if ( $event->type == $type->id ) {
					$event->type = $type->short_name;    //add type short name to event
				}
			}
		}
	}

	return $todayEvents; //return event with types
}

function addReadebleTime( $todayEvents ) {
	foreach ( $todayEvents as $event ) {
		$event->start_time = "Начало пары: " . date( "H:i", $event->start_time + 3600 * 3 );
		$event->end_time   = "Конец пары: " . date( "H:i", $event->end_time + 3600 * 3 );
	}

	return $todayEvents;
}

function getStringTodayEvents($today_events){
	$reply = "";

	foreach ($today_events as $event){
		$reply .= $event->subject_id."\n";
		$reply .= $event->type."\n";
		$reply .= $event->auditory."\n";
		$reply .= $event->start_time."\n";
		$reply .= $event->end_time."\n";
		if($event->teachers) {
			$reply .= "Преподаватель: " . $event->teachers . "\n";
		}
		$reply .= $event->auditory."\n\n";
	}

	return $reply;
}

?>