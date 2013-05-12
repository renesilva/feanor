<?php
/**
 * Session library
 *
 * @package    Feanor
 * @author     Rene Silva <rsilva@eresseasolutions.com>
 * @copyright  Copyright (c) 2013 Eressea Solutions Development Team
 * @license    MIT License
 */
namespace Feanor;

class Session{
	public function __construct () {
		session_start();
	}
	
	public static function get ($name, $default = '') {
		if(isset($_SESSION[$name])){
			return $_SESSION[$name];
		} else {
			return $default;
		}
	}

	public static function set ($name, $value) {
		$_SESSION[$name] = $value;
	}

	public static function delete ($name){
		if(isset($_SESSION[$name])){
			unset($_SESSION[$name]);
		}
	}

	public static function destroy (){
		session_unset();
		session_destroy();
		session_write_close();
		setcookie(session_name(),'',0,'/');
		session_regenerate_id(true);
	}

	public static function regenerate (){
		session_regenerate_id(true);
	}
}