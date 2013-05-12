<?php
/**
 * Clase core del framework.
 *
 * Inicializa varias clases que nos servirán más adelante
 *
 * @package    Feanor
 * @author     Rene Silva <rsilva@eresseasolutions.com>
 * @copyright  Copyright (c) 2013 Eressea Solutions Development Team
 * @license    MIT License
 */
namespace Feanor;

class FW{
	/**
	 *
	 * @var Router
	 */
	public $router;

	/**
	 *
	 * @var View
	 */
	public $view;

	/**
	 *
	 * @var DB
	 */
	public $db;

	/**
	 *
	 * @var Config
	 */
	public $config;

	/**
	 *
	 * @var Input
	 */
	public $input;

	/**
	 *
	 * @var Session
	 */
	public $session;

	/**
	 *
	 * @var \CI_Security
	 */
	public static $security;

	/**
	 *
	 * @var string
	 */
	public static $loaded_module = '';

	public function __construct () {
		if(!defined('BASEPATH')){
			define('BASEPATH',__DIR__.'/../');
		}

		//Loading Codeigniter para los tests
		require_once(__DIR__.'/External_Libraries/CI/codeigniter_functions.php');
		require_once(__DIR__.'/External_Libraries/CI/Security.php');
		self::$security = new \CI_Security();

		//helpers
		require_once(__DIR__.'/helpers/text.php');
		require_once(__DIR__.'/helpers/files.php');
		require_once(__DIR__.'/helpers/fw.php');
	}

	public function init(){

		$this->config = new Config();
		require_once(BASEPATH.'app/config/config.php');
		if(file_exists(BASEPATH.'app/config/custom_config.php')){
			require_once(BASEPATH.'app/config/custom_config.php');
		}

		$this->router = new Router();
		$this->view = new View();
		$this->db = new DB();
		$this->input = new Input();
		$this->session = new Session();

		//BASE URL
		$partial_path = $_SERVER['REQUEST_URI'];
		if(isset($_SERVER['PATH_INFO'])){
			//damos la vuelta ambas variables y borro el primer path_info en caso
			//de que exista uno
			$path_info = strrev($_SERVER['PATH_INFO']);
			$request_uri = strrev($_SERVER['REQUEST_URI']);
			$pos = strpos($request_uri, $path_info);
			if($pos !== false){
				$partial_path = strrev(substr($request_uri, strlen($path_info))).'/';
			}
		}
		$this->base_url = 'http://'.$_SERVER['HTTP_HOST'].$partial_path;
		View::set('fw_base_url', $this->base_url);

		//GLOBALS
		define('BASE_URL',$this->base_url);
		define('BASE_DOMAIN',$_SERVER['HTTP_HOST']);
		define('BASE_PATH',$partial_path);

		//ROUTER INIT
		$this->router->init();

		if(FW::$loaded_module != ''){
			//Modules
			$modules = Config::get('modules',array());
			if(!empty($modules)){
				foreach($modules as $module){
					if(file_exists(BASEPATH.'app/modules/'.$module.'/config/config.php')){
						require_once(BASEPATH.'app/modules/'.$module.'/config/config.php');
					}
					if(file_exists(BASEPATH.'app/modules/'.$module.'/config/custom_config.php')){
						require_once(BASEPATH.'app/modules/'.$module.'/config/custom_config.php');
					}
				}
			}
		}

		$this->router->execute();

		//automatic view layout en caso de que no haya un render()
		if(!View::$rendered){
			$base = BASEPATH.'app/views/';
			if(FW::$loaded_module != ''){
				$base = BASEPATH.'app/modules/'.FW::$loaded_module.'/views/';
			}
			$controller_explode = explode('\\',$this->router->get_controller());
			$layout = strtolower(end($controller_explode)).'/'.$this->router->get_method().'.php';
			$template = $base.$layout;
			if(file_exists($template)){
				View::$layout = $layout;
				View::render();
			}
			
		}

	}

	/*************************************************************/
	/***********************FUNCIONES*STRUCTURE*******************/
	/*************************************************************/

	/**
	 * Instala la aplicación desde un archivo de configuración previamente definido.
	 *
	 * @param array	$params['tables'] Tablas o modelos que serán instalados.
	 *						Si es array se asume que es una estructura y se procede
	 *						a instalarlo :)
	 * @param string	$params['db'] Nombre de la base de datos para borrarla e
	 *						instalar las cosas sobre esa
	 * @param string	$params['sql_file'] Archivo que puede ser adjuntado en el
	 *						momento de la instalación.
	 * @return void
	 */
	public static function install ($params = null) {

		$query = '';
		$tables = array();
		//pasamos tabla por tabla
		foreach ($params['tables'] as $table_name => $table) {

			$structure = new Structure($table,$table['table_name']);
			$tables[] = $table['table_name'];

			//En estas variables se guardaran el codigo sql para cada tipo de sentencia.
			$primaryKeys = '';
			//Verficamos si es que el parámetro auto_increment es yes
			if ($table['keys']['auto_increment']) {
				$primaryKeys = '`'.$table['keys']['key'][0].'` INTEGER AUTO_INCREMENT NOT NULL PRIMARY KEY';
			} else {
				$buffer = array();
				foreach ($table['keys']['key'] as $pk) {
					$primaryKeys .= '`'.$pk.'` INTEGER NOT NULL, ';
					$buffer[] = (string) $pk;
				}
				$primaryKeys .= 'PRIMARY KEY(' . implode(",", $buffer) . ')';
			}

			//Pasamos por cada tabla y verificamos si es foreign key, y si es parte o no de la primary key
			$camposMostrar = array();
			foreach ($structure->fields as $field){
				if(isset($field->params['dont_insert_in_query']) && $field->params['dont_insert_in_query']){

				} else {
					$camposMostrar[] = $field->_install();
				}
				//$camposMostrar[] = $fieldName . ' integer not null default 0';
			}
$query = $query.'
CREATE TABLE `'.DB::dbprefix($table['table_name']).'`
(' . $primaryKeys .', '. implode(',',$camposMostrar) . ') ENGINE=MyISAM;';//MyISAM//InnoDB
		}

		//meta tables
		if(isset($params['meta_tables'])){
			foreach($params['meta_tables'] as $meta_table_name){
				$query .= '
					CREATE TABLE `'.DB::dbprefix($meta_table_name).'_meta`
					(
						`id_'.$meta_table_name.'_meta` INTEGER AUTO_INCREMENT NOT NULL PRIMARY KEY,
						`id_'.$meta_table_name.'`  INTEGER  not null ,
							FOREIGN KEY(`id_'.$meta_table_name.'`) REFERENCES `'.$meta_table_name.'`(`id_'.$meta_table_name.'`),
						`meta_key`  varchar (250)  not null ,
						`meta_value`  text  not null ,
						`autoload`  integer  null  default "0" ,
						`sw` TINYINT(1) not null default 1
					) ENGINE=MyISAM;';//MyISAM//InnoDB
			}
		}

		
		try{
			$buffer_query_debug = '';

			DB::beginTransaction();
			//USAR EN CASO DE TENER EL PODER DE LA BD, DE LO CONTRARIO ELIMINAR MANUALMENTE
			if (isset($params['db'])) {
				$sql = 'drop database `'.$params['db'].'`';
				$result = DB::query($sql);
				$sql = 'create database `'.$params['db'].'`';
				$result = DB::query($sql);
				$sql = 'use `'.$params['db'].'`';
				$result = DB::query($sql);
			} else {
				$reverse_tables = array_reverse($tables);
				foreach ($reverse_tables as $table_name) {
					$droptable = 'drop table IF EXISTS `'.DB::dbprefix($table_name).'`';
					$result = DB::query($droptable);
					echo $droptable.'<br/>';
				}

				if(isset($params['meta_tables'])){
					foreach($params['meta_tables'] as $meta_table_name){
						$droptable = 'drop table IF EXISTS `'.DB::dbprefix($meta_table_name).'_meta`;';
						$result = DB::query($droptable);
						echo $droptable.'<br/>';
					}
				}

				if(isset($params['extra_tables'])) {
					foreach ($params['extra_tables'] as $table_name) {
						$sql = 'drop table IF EXISTS `'.DB::dbprefix($table_name).'`';
						$result = DB::query($sql);
					}
				}
			}
			$array = explode(';', $query);
			for ($i = 0; $i < sizeof($array) - 1; $i ++) {
				$result = DB::query($array[$i]);
				$buffer_query_debug .= $array[$i];
			}
			//Si es que existe un archivo adjunto lo ejecutamos. El separador debe ser ";"
			if (isset($params['sql_file'])) {
				$sql_installers = array();
				if(is_array($params['sql_file'])){
					$sql_installers = $params['sql_file'];
				} else {
					$sql_installers[] = $params['sql_file'];
				}

				foreach($sql_installers as $sql_installer){
					if (file_exists($sql_installer)) {
						$data = file_get_contents($sql_installer);
						//$array = explode(';', $data);
						$array = preg_split('/;[\n\r]+/',$data);
						for ($i = 0; $i < sizeof($array) - 1; $i ++) {
							$result = DB::query($array[$i]);
							$buffer_query_debug .= $array[$i];
							if ($result->rowCount() < 1) {
								echo "Query sin salida: " . $array[$i];
							}
						}
					}
				}

			}
			DB::commit();
		} catch (\PDOException $e){
			echo $e->getMessage();
			DB::rollBack();
		}

		return $buffer_query_debug;
	}

	/**
	 * Generar un form
	 * @param array $_structure Respuesta de un get_structure de un model
	 * @param string $_title Título
	 * @param array $_ajax_functions Botones a mostrar
	 * @param array $params Otros parámetros
	 * @param string $_display_type Forma en la que se muestra la tabla (esto afecta a los campos)
	 * @param array $_restriction Campos que no se mostraran de una manera como todos los otros
	 * @param integer $_id ID del row que es de una Tabla
	 * @param string $_help Texto de ayuda
	 * @param string $_table_name Nombre de la tabla en caso de que no sea el mismo que del modelo/estructura
	 * @return array
	 */
	public static function generate_form (
		$_structure, $_title = '', $_ajax_functions = array (), $params = array (),$_display_type = 'form_edit',
		$_restriction = array (), $_id = 0, $_help = '', $_table_name = '') {

		if($_table_name == '') $_table_name = $_structure['table_name'];

		! isset($params['form_action']) ? $form_action = '/' : $form_action = $params['form_action'];
		//Definimos el Template del generador,
		//este puede cambiar en caso de enviar otro parámetro
		! isset($params['template']) ? $template = 'Feanor/core/views/forms.php' : $template = $params['template'];
		! isset($params['html_before_submit']) ? $html_before_submit = '' : $html_before_submit = $params['html_before_submit'];
		! isset($params['html_before_table']) ? $html_before_table = '' : $html_before_table = $params['html_before_table'];
		! isset($params['separator']) ? $separator = '' : $separator = $params['separator'];
		//valores escondidos en el form
		! isset($params['hidden_values']) ? $hidden_values = array() : $hidden_values = $params['hidden_values'];

		//Forma para mostrar por defecto
		if ($_display_type == 'table_edit') {
			$template = 'Feanor/core/views/generateformtable.php';
		}

		//nonce



		/*$nonce = '';
		if(isset($params['nonce']) && $params['nonce'] === true){
			$log = new Melian\Log();
			$log->params['id_user'] = $this->current_user->id_user;
			$log->add_log_entry();
			$nonce = $log->get_nonce();
		}*/


		//que campos serán mostrados
		$fields_show = array();
		//valores de esos campos en la Base de datos
		$values = array();

		$structure = new Structure($_structure,$_table_name,$_display_type,$_restriction);
		if ($_structure['keys']['auto_increment'] && $_id != 0) {
			$primaryKey = (string) $_structure['keys']['key'][0];
			$query = 'SELECT ' . implode(',', $structure->select_column). '
			FROM `'.DB::dbprefix($_table_name).'` WHERE `'.$primaryKey.'`=' . $_id;
			$result = DB::query($query);
			if ($result->rowCount() > 0) {
				foreach ($result->fetch() as $k => $v) {
					$values[$k] = $v;
				}
				foreach($structure->fields as $field_name => $field){
					$field->current_id = $_id;
					$field->current_object_name = $_table_name;
					if(isset($field->params['meta']) && $field->params['meta']){
						$values[$field_name] = get_metadata($field->name,$_table_name,$_id);
					}
				}
			}
		}
		foreach ($structure->fields as $field_name => $field){
			$valor = null;
			if (array_key_exists($field_name, $values)) {
				$valor = $values[$field_name];
			}
			$fields_show[] = $field->_display($valor,$values);
		}

		$view = new View();
		
		$view->part_set('params', $params);//just in case

		$view->part_set('table_name', $_table_name);
		$view->part_set('title', $_title);
		$view->part_set('form_action', $form_action);
		$view->part_set('ajax_function', $_ajax_functions);
		$view->part_set('campos', $fields_show);
		$view->part_set('html_before_submit', $html_before_submit);
		$view->part_set('html_before_table', $html_before_table);
		$view->part_set('hidden_values', $hidden_values);
		//$view->part_set('nonce', $nonce);
		$view->part_set('help', $_help);
		$view->part_set('id', $_id);

		//id randomico
		$rand = mt_rand(0, 32);
		$random = substr(md5($rand . time()), 0, 7);
		$view->part_set('random', $random );
		
		$view->part_layout = $template;
		return array('id'=>$_table_name.'_div_'.$random,'content'=>$view->part_render());

	}

	/**
	 *
	 * @param Structure $structure
	 * @param string $mod
	 * @param array $values
	 * @param array $restriction
	 * @return array
	 */
	public static function validate($structure,$mod,$values,$restriction){

		require_once(__DIR__.'/External_Libraries/CI/CI.php');
		require_once(__DIR__.'/External_Libraries/CI/Lang.php');
		require_once(__DIR__.'/External_Libraries/CI/Form_validation_cessil.php');
		require_once(__DIR__.'/External_Libraries/CI/language/form_validation_lang.php');

		//de los que si pasan
		$config = array();
		//de los que no pasan
		$config_restriction = array();

		//extra_values, porque un solo valor de _insert o _update NO ES SUFICIENTE!!
		//just kidding, es para los objetos ¬¬
		$extra_values = array();
		$extra_fields = array();

		foreach ($structure->fields as $field_name => $field){
			if ($field->name != 'sw' ) {
				$rules = '';
				if ($mod == 'mod' && isset($field->params['conditions_mod'])) {
					//condiciones diferentes al ser modificado
					$rules = $field->params['conditions_mod'];
				} else {
					$rules = $field->conditions;
				}
				$s = array (
							'field' => $field_name,
							'name'  => $field_name,
							'label' => $field_name,
							'rules' => $rules,
				);
				foreach ($field->params as $a => $b) {
					if(!is_array($b) && !is_object($b)){
						$s[$a] = (string) $b;
					}
				}
				if (array_key_exists($field_name, $restriction)) {
					//El valor de RESTRICTION va como campo default
					$s['restriction'] = $restriction [$field_name];
					$s['value'] = $restriction [$field_name];
					$config_restriction[] = $s;
				} else {
					if(isset($values[$field_name])){
						if($mod == 'add'){
							$a = $field->_insert($values[$field_name]);
						} elseif($mod == 'mod'){
							$a = $field->_update($values[$field_name]);
						}
						if(is_array($a)){
							$extra_values = array_merge($extra_values,$a['extra_values']);
							$extra_fields = array_merge($extra_fields,$a['extra_fields']);
							$s['value'] = $a['value'];
						} else {
							$s['value'] = $a;
						}
					} else {
						$s['value'] = '';
					}
					$config[$field_name] = $s;
				}
			}
		}

		$form_validation_cessil = new \Form_validation_cessil();

		$form_validation_cessil->set_rules($config);

		$run = $form_validation_cessil->run($values);

		$errors = $form_validation_cessil->error_string();

		//destruyendo el objeto
		unset($form_validation_cessil);

		return array(
			 'passed' => $run,
			 'config'=>$config,
			 'config_restriction'=>$config_restriction,
			 'extra_values'=>$extra_values,
			 'extra_fields'=>$extra_fields,
			 'errors'=>$errors
		);
	}
	
	/**
	 * Para Adicionar data
	 *
	 * @param array $_structure Respuesta de un get_structure de un model
	 * @param array $_values Valores a ingresarse
	 * @param array $_restriction Restricciones de ciertos campos, osea para que estos no sean ingresados
	 * @param integer $_id ID para editar en caso de que se edite algo
	 * @param string $_table_name Nombre de la tabla, en caso
	 * de que el modelo o estructura no tenga el mismo nombre que la tabla
	 * @return array [$errors] evaluar si es === false, si no entonces tienes errores
	 */
	public static function add_data ($_structure, $_values, $_restriction = array(), $_id = 0, $_table_name = '') {

		if($_table_name == '') $_table_name = $_structure['table_name'];
		if($_id == 0) $mod = 'add'; else $mod = 'mod';

		//en caso de que tengamos una estructura alterna
		$structure = new Structure($_structure,$_table_name,'',$_restriction);

		$validate = self::validate($structure, $mod, $_values, $_restriction);

		if ($validate['passed'] === TRUE) {
			$errors = false;

			//de los que no pasan
			$config_restriction = $validate['config_restriction'];

			//extra_values, porque un solo valor de _insert o _update NO ES SUFICIENTE!!
			//just kidding, es para los objetos ¬¬
			$extra_values = $validate['extra_values'];
			$extra_fields = $validate['extra_fields'];

			$config = array_merge($validate['config'],$config_restriction);
			//borramos para la siguiente validación
			$fields = $extra_fields;
			$new_values = $extra_values;
			foreach ($config as $cc) {
				if( !(isset($cc['dont_insert_in_query']) && $cc['dont_insert_in_query']) ){
					$type = $cc['type'];
					if($type != 'sw'){
						$fields[] = (string) '`'.$cc['field'].'`';
						$new_values[] = $cc['value'];
					}
				}
			}

			if ($_structure['keys']['auto_increment']) {

				$primaryKey = (string) $_structure['keys']['key'][0];
				if ($mod == 'add') {
					$sqlCode = 'insert into `'.DB::dbprefix($_table_name).'` ('.implode(',',$fields).')
					values (';
					$sizeof_values = sizeof($new_values);
					for($ii = 0; $ii < $sizeof_values;$ii++){
						$sqlCode .= '?';
						if($ii != ($sizeof_values-1)){
							$sqlCode .= ',';
						}
					}
					$sqlCode .= ')';
				} else {
					$sqlCode = 'update `'.DB::dbprefix($_table_name).'` set ';
					$sizeof_values = sizeof($new_values);
					for($ii = 0; $ii < $sizeof_values;$ii++){
						$sqlCode .= $fields[$ii].'=? ';
						if($ii != ($sizeof_values-1)){
							$sqlCode .= ',';
						}
					}
					$sqlCode .= 'where sw=1 and ' . $primaryKey . '=' . $_id;
				}
				//ejecución del query
				$result = DB::prepare($sqlCode);
				$result->execute($new_values);
				if ($result->rowCount()>0 && $mod == 'add') {
					//Seleccionamos el último registro válido.
					$_id = DB::lastInsertId();
				} elseif ($result->rowCount() == 0 && $mod == 'mod'){
					$errors = '<p>No existe el ID</p>';
				}

			}

			if($_id != 0){
				foreach ($structure->fields as $field_name => $field){
					if ($field->name != 'sw' ) {
						$value = '';
						$field->current_id = $_id;
						$field->current_object_name = $_table_name;

						if(isset($_values[$field_name])){
							$value = $_values[$field_name];
						}
						$field->_after_add_data_query($value);
					}

					//save meta!!!!!
					if(isset($field->params['meta']) && $field->params['meta']){
						//La metadata que se guarda de estructuras debe tener un autoload=true (1)
						$saved = save_metadata($field->name,$config[$field->name]['value'],$_table_name,$_id,1);
					}
				}
			}
			
			return array(
					'errors' => $errors,
					'id' => $_id
			);

		} else {
			$errors = $validate['errors'];
			return array(
				'errors' => $errors
			);
		}
	}

}
