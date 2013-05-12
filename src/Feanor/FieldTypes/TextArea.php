<?php
/**
 * Clase TextArea
 * 
 * @package    Feanor/FieldTypes
 * @author     Rene Silva <rsilva@eresseasolutions.com>
 * @copyright  Copyright (c) 2013 Eressea Solutions Development Team
 * @license    MIT License
 */
namespace Feanor\FieldTypes;

class TextArea extends \Feanor\FieldType {

	protected $rows = 20;
	protected $cols = 5;
	protected $class = '';

	
	public function __construct ($_params = null,$_mode = null) {
		parent::__construct($_params,$_mode);
		if (isset($this->params['rows']))  $this->rows = $this->params['rows'];
		if (isset($this->params['cols']))  $this->cols = $this->params['cols'];
		if (isset($this->params['wyswyg']))  $this->class = 'wyswyg_textarea';
	}
	
	public function _display($valor, $other_values = array()){
		$buffer = '';
		if ($this->mode == 'form_edit' || $this->mode == 'form_static') {
			$buffer = $buffer.$this->label.'<div class="controls">';
		}
		if ($this->mode === 'table_static') {
			$buffer .= strip_tags(word_limiter($valor, 20));
		} elseif ($this->mode == 'form_edit' || $this->mode == 'table_edit')	{
			if ($this->required) {
				$this->class .= ' required';
			}
			$data = array(
				'name' => $this->name, 
				'id' => $this->name, 
				'value' => $valor, 
				'rows' => $this->rows, 
				'cols' => $this->cols,
				'class' => $this->class
			);
			$buffer .= form_textarea($data);
		} elseif ($this->mode == 'form_static' || $this->mode == 'table_static_all') {
			$buffer .= $valor;
		}
		
		if ($this->mode === 'form_edit' || $this->mode === 'form_static') {
			if(isset($this->params['help']))	$buffer .= '<p class="help-block">'.$this->params['help'].'</p>';
			$buffer .= '</div>';
		}
		
		return $buffer;
		
		
		/**
		 * @todo Revisar estos tipos de datos META
		 */
		/*if($this->mode == 'show_meta_table_static')	{
			
			$array_values = json_decode($this->value);
			if(!empty($array_values)){
				foreach ($array_values[0] as $ar_label => $ar_value){
					$this->buffer.='<strong style="text-transform:capitalize;">'.$ar_label.':</strong>';
					$this->buffer.=' '.$ar_value.'<br/>';
				}
			}
		}
		
		if($this->mode == 'show_meta_static')	{
			$array_values = json_decode($this->value);
			if(!empty($array_values)){
				foreach ($array_values[0] as $ar_label => $ar_value){
					$this->buffer = $this->buffer . 
						'<tr><td><label class="meta_label_'.$ar_label.'" for="' . $ar_label . '">' . $ar_label. ': </label>' .'</td>
						  <td class="meta_value_'.$ar_label.'">'. $ar_value  ;
					$this->buffer = $this->buffer . '</td></tr>';
				}
			}
		}
		
		if($this->mode == 'show_meta_edit')	{
			$data = array(
					'name' => $this->name, 
					'id' => $this->name, 
					'value' => $this->value, 
					'rows' => $this->rows, 
					'cols' => $this->cols
				);
				if (preg_match('/required/', $this->conditions)) {
					$data['class'] = 'required';
				}
				$this->buffer .= form_textarea($data);
		}*/
		
	}
	public function _install () {
		$tipo = ' text ';
		if ($this->required) {
			$tipo .= ' not null ';
		} else {
			$tipo .= ' null ';
		}
		return '`'.$this->name.'` '.$tipo;
	}
}