<?php
/**
 * Created by PhpStorm.
 * User: d.goncharov
 * Date: 05.02.18
 * Time: 16:25
 */

include( 'DbConnection.php' );		//Подключаем класс для работы с БД


class Link{

	function setLink($text) {

		$explodeText = explode(' ', $text);

		$tableName = str_replace( '/', '', $explodeText[0]);
		$title = $explodeText[1];
		$url = $explodeText[2];

		if ($page_content = file_get_contents ($url)) {
			preg_match( "|<title>(.*)</title>|sUSi", $page_content, $matches);
			$link_title = $matches[1];
			$link_title = trim(mb_substr(explode('—', $link_title)[0], 0, 100));
			try {

				$connection = new DbConnection(false);
				$result = $connection->insert($tableName, array(
					'title' => $title,
					'link' => $url,
					'link_title' => $link_title,
				));

				if($result){
					return 'Link are save';
				}

				return 'Link must be unique. Or table with name '. $tableName .' is\'t found';

			} catch (PDOException $e) {
				error_log($e);
				return 'Link isn\'t save! Link must be unique. Some error in db!';
			}
		}

		return 'Link doesn\'t work!';
	}

	function getLinks($text){
		$explodeText = explode(' ', $text);

		$tableName = str_replace( '/', '', $explodeText[0]);
		$title = $explodeText[1];

		try {
			$connection = new DbConnection(false);
			$result = $connection->selectAll($tableName, array(
				'title' => $title,
			));

			if($result){
				return $result;
			}

			return 'Nothing to found. Or table with name '. $tableName .' is\'t found';

		} catch (PDOException $e) {
			error_log($e);
			return 'Some error in db!';
		}
	}

	function showLinks($entity){

		$resultMessage = "";

		if(is_string($entity)){
			return $resultMessage = $entity;
		}

		foreach ($entity as $link){
			$url = $link['link'];
			$title = $link['link_title'];

			if (file_get_contents ($url)) {
				$resultMessage .= "<a href='$url'>$title</a>\n";
			}
		}

		if(count($resultMessage) == 0){
			$resultMessage = 'All link in database doesn\'t work. Try to add one.';
		}

		return $resultMessage;
	}

}