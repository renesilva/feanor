<?php
/**
 * Clase TextInput
 *
 * @package    Feanor/FieldTypes
 * @author     Rene Silva <rsilva@eresseasolutions.com>
 * @copyright  Copyright (c) 2013 Eressea Solutions Development Team
 * @license    MIT License
 */
namespace Feanor\FieldTypes;

class TextInput extends \Feanor\FieldType {
	
	protected $size = 10;
	protected $max_length = 150;
	
	public function __construct ($_params = null,$_mode = null) {
		parent::__construct($_params,$_mode);
		$max_length = 30;
		if (preg_match('/max_length\[(.*?)\]/', $this->conditions, $max_length)) {
			$this->max_length = $max_length[1];
		}
		if (isset($this->params['size']))  $this->size = $this->params['size'];
	}

	public function _display($valor, $other_values = array()){
		
		$buffer = '';
		$label = '';
	
		if($this->mode !== 'form_edit' && $this->mode !== 'table_edit' && $valor !== '') {
			if ($this->subtype === 'datetime') {
				$valor = \nicetime($valor);
			}
		}
		
		if ($this->mode === 'form_edit' || $this->mode === 'form_static') {
			$buffer = $buffer.$this->label.'<div class="controls">';
		}
		
		if ($this->mode === 'table_static') {
			$buffer .= $valor;
		} elseif ($this->mode === 'form_edit' || $this->mode === 'table_edit') {
			$data = array(
				'name' => $this->name,
				'value' => $valor,
				'maxlength' => $this->max_length,
				'size' => $this->size
			);
			//Diferentes tipos de datos
			$clases = array();
			if($this->required) {
				$clases[] = 'required';
			}
			switch ($this->subtype){
				case 'float':
					$clases[] = 'float';
					break;
				case 'integer':
					$clases[] = 'integer';
					break;
				case 'date':
					$clases[] = 'date';
					break;
				case 'datetime':
					$clases[] = 'datetime';
					break;
				case 'fileupload':
					$clases[] = 'fileupload';
					break;
				
			}
			$data['class'] = implode(' ', $clases);

			if(isset($this->params['appended']) && $this->params['appended'] != ''){
				$buffer = $buffer.'<div class="input-append">'.
						  \form_input($data).'<span class="add-on">'.$this->params['appended'].'</span></div>';
			} else {
				$buffer .= \form_input($data);
			}

		} elseif ($this->mode === 'form_static' || $this->mode === 'table_static') {
			//en caso de que sea vacío
			if(trim($valor) == '') {
				$valor = '-';
			}
			$buffer = $buffer . $valor;
		}
		
		if ($this->mode === 'form_edit' || $this->mode === 'form_static') {
			if(isset($this->params['help']))	$buffer .= '<p class="help-block">'.$this->params['help'].'</p>';
			$buffer .= '</div>';
		}

		
		return $buffer;
	}
	
	public function _install () {
		$buffer = '';
		$val = '';
		
		switch($this->subtype){
			case '':
				if (preg_match('/max_length\[(.*?)\]/', $this->conditions, $val)) {
					$buffer = ' varchar (' . $val[1] . ') ';
				} else {
					$buffer = ' varchar (150) ';
				}
				break;
			case 'float':
				$buffer = ' float ';
				break;
			case 'integer':
				$buffer = ' integer ';
				break;
			case 'decimal':
				$buffer = ' decimal(12,2) ';
				break;
			case 'datetime':
				$buffer = ' datetime ';
				break;
			case 'date':
				$buffer = ' date ';
				break;
			case 'boolean':
				$buffer = ' TINYINT(1) ';
				break;
		}
	
		if ($this->required) {
			$buffer .= ' not null ';
		} else {
			$buffer .= ' null ';
		}
		
		if(isset($this->params['default'])) {
			$buffer.=' default "'.$this->params['default'].'" ';
		}
		
		return '`'.$this->name.'` '.$buffer;
	}
	
	
}