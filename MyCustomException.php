<?php
/**
 * Created by PhpStorm.
 * User: d.goncharov
 * Date: 09.02.18
 * Time: 17:18
 */

class MyCustomException extends Exception{

	public function __construct($message, $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}

	// Переопределим строковое представление объекта.
	public function __toString() {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}

	public function customFunction() {
		echo "Мы можем определять новые методы в наследуемом классе\n";
	}
}