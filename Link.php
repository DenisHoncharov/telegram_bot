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

			$link_title = mb_convert_encoding($matches[1], "UTF-8");

			//delete '?' if encoding is false
			$link_title = str_replace ( '?', '', $link_title);

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
			$link_id = $link['id'];
			$url = $link['link'];
			$title = $link['link_title'];

			if (file_get_contents ($url)) {
				$resultMessage .= "$link_id <a href='$url'>$title</a>\n";
			}
		}

		if(count($resultMessage) == 0){
			$resultMessage = 'All link in database doesn\'t work. Try to add one.';
		}

		return $resultMessage;
	}

	function deleteLinks($text){
		$explodeText = explode(' ', $text);

		$tableName = str_replace( '/', '', $explodeText[0]);
		$link_id = $explodeText[2];

		try {
			$connection = new DbConnection(false);
			$result = $connection->deleteRow($tableName, array(
				'id' => $link_id,
			));

			if($result){
				return $result;
			}

			return 'Can\'t found row with id = "'. $link_id .'". Or table with name '. $tableName .' is\'t found';

		} catch (PDOException $e) {
			error_log($e);
			return 'Some error in db!';
		}

	}

	function getGifLink($text){
		$tag = trim(str_replace( '/gif', '', $text));

		$url = 'https://api.giphy.com/v1/gifs/random?api_key=9GBOuR3x9riYGz1ZLjTu3ypxWCuxjdZD&tag='. urlencode($tag) .'&rating=G';

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$gifResponse = curl_exec($ch);
		curl_close($ch);

		if(!$gifResponse){
			die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
		}

		$giphyBody = json_decode($gifResponse);

		$foundedGif = $giphyBody->data->image_mp4_url;

		return $foundedGif;
	}
}