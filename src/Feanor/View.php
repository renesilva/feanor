<?php
/**
 * Básicamente nos facilita el trabajo de mostrar templates
 *
 * @package    Feanor
 * @author     Rene Silva <rsilva@eresseasolutions.com>
 * @copyright  Copyright (c) 2013 Eressea Solutions Development Team
 * @license    MIT License
 */
namespace Feanor;

class View{
	/**
	 * Nombre del template
	 * @var string
	 */
	public static $layout = '';
	public $partial_layout = '';

	/**
	 * Variables
	 * @var array
	 */
	private static $vars = array();
	private $partial_vars = array();

	/**
	 * Para saber si ya fue renderizado y ejecutar la renderización automática
	 * @var boolean
	 */
	public static $rendered = false;
	
	/**
	 * Función que nos permite definir variables para el template
	 *
	 * @param string $var
	 * @param mixed $value
	 */
	public static function set($var, $value = ''){
		if(is_array($var)){
			self::$vars = $var + self::$vars;
		} else {
			self::$vars[$var] = $value;
		}
	}
	public function partial_set($var, $value = ''){
		if(is_array($var)){
			$this->partial_vars = $var + $this->partial_vars;
		} else {
			$this->partial_vars[$var] = $value;
		}
	}

	/**
	 * Para saber si fue setted or not la variable_name
	 *
	 * @param string $variable_name
	 * @return mixed
	 */
	public static function get($variable_name){
		if(isset(self::$vars[$variable_name])){
			return self::$vars[$variable_name];
		}
		return false;
	}
	public function partial_get($variable_name){
		if(isset($this->partial_vars[$variable_name])){
			return $this->partial_vars[$variable_name];
		}
		return false;
	}
	/**
	 * Función que nos permite imprimir el layout
	 *
	 * @param boolean $return Si debemos retornar el layout a una variable
	 * @return string En caso de que $return sea true nos entrega el cached del layout
	 * @throws \Exception Error en caso de que no exista el template
	 */
	public static function render ($return = false) {
		if($template = self::render_prepare(self::$vars, self::$layout)){
			extract(self::$vars);
			ob_start();
			include($template);
			$buffer = ob_get_clean();
			if (!$return){
				echo $buffer;
			} else {
				self::$rendered = true;
				return $buffer;
			}
		}
	}
	public function partial_render(){
		if($template = self::render_prepare($this->partial_vars, $this->partial_layout)){
			extract($this->partial_vars);
			ob_start();
			include($template);
			$buffer = ob_get_clean();
			return $buffer;
		}
	}

	private static function render_prepare ($vars,$layout){
		if(strpos($layout, '.php') === false){
			$layout .= '.php';
		}
		$base = BASEPATH.'app/views/';
		if(FW::$loaded_module != ''){
			$base = BASEPATH.'app/modules/'.FW::$loaded_module.'/views/';
		}
		if(strpos($layout,'Feanor/core/views/') !== false){
			$base = __DIR__.'core/views/';
		}
		$template = $base.$layout;
		try{
			if(!file_exists($template)){
				 throw new \Exception('No existe el view <strong>'.$template.'</strong>');
			} else {
				return $template;
			}
		} catch(\Exception $e){
			echo 'Error: '.$e->getMessage().'<br/>';
			var_dump(debug_backtrace());
			return false;
		}
	}


	public static function render_json($array){
		header('Content-type: application/json');
		echo json_encode($array);
	}
	
}