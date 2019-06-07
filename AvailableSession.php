<?php
/**
 * Created by PhpStorm.
 * User: d.goncharov
 * Date: 02.02.18
 * Time: 17:52
 */

class AvailableSession{

	function getReadableTodayEvents() {
		$timeTableData = self::getAllEvents();    //get all events

		$events = $timeTableData->events;

		$today_events = self::getTodayEvents( $events );    //get today events

		$today_events = self::getComplitedEvents( $today_events, $timeTableData );  //make readable Events

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

		$todayEvents = self::addTeachersToEvents( $todayEvents, $teachers );    //add teachers
		$todayEvents = self::addSubjectToEvents( $todayEvents, $subjects );     //add subjects
		$todayEvents = self::addTypeToEvents( $todayEvents, $types );           //add type
		$todayEvents = self::addReadebleTime( $todayEvents );                   //add readable time

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
}