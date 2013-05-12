<?php
/**
 * Base de Log
 *
 * @package    Feanor
 * @author     Rene Silva <rsilva@eresseasolutions.com>
 * @copyright  Copyright (c) 2013 Eressea Solutions Development Team
 * @license    MIT License
 */
namespace Feanor\Core\Models;

class Log extends \Feanor\Model{
	/**
	 * @Key{"auto_increment":true}
	 */
	public $id_log = 0;
	/**
	 * @Field{"type":"sw"}
	 */
	public $sw = 1;

	public static $model_info = array();
	public function __construct ($attributes_or_id = array ( )) {
		parent::__construct($attributes_or_id);
	}
}