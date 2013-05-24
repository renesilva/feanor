<?php
/**
 * Clase abstracta que define a los tipos de campo, Field Types
 *
 * @package    Feanor
 * @author     Rene Silva <rsilva@eresseasolutions.com>
 * @copyright  Copyright (c) 2013 Eressea Solutions Development Team
 * @license    MIT License
 */
namespace Feanor;

abstract class FieldType
{
    /**
     * Variable en caso de que lo necesitemos más adelante
     * @var array
     */
    public $params;

    /**
     * Modo en el que inicia, puede ser form_edit, form_static, table_edit o table_static
     * @var string
     */
    public $mode;

    /**
     * Current ID del objeto que tiene la estructura
     * @var integer
     */
    public $current_id = 0;

    /**
     * Lo mismo que el current_id
     * @var string
     */
    public $current_object_name = 'user';

    /**
     * Nombre del tipo de dato
     * @var string
     */
    public $name;

    /**
     * Subtipo del tipo de dato
     * @var string
     */
    public $subtype = '';

    /**
     * Casi siempre string, valor del tipo de dato (solo en forms, no tanto en tablas)
     * @var mixed
     */
    public $value = '';

    /**
     * Condiciones para la verificación de datos
     * @var string
     */
    public $conditions = '';

    /**
     * Si es requerido o no
     * @var boolean
     */
    public $required = false;

    /**
     * Separador de campo a campo
     * @var string
     */
    public $separator = '</td><td>';

    /**
     * Label del tipo de dato
     * @var string
     */
    public $label = '';

    public function __construct ($_params = null, $_mode = null)
    {

        $this->params = $_params;
        $this->mode = $_mode;

        $this->name = $this->params['name'];

        if (isset($this->params['subtype'])) {
            $this->subtype = $this->params['subtype'];
        }
        if (isset($this->params['separator'])) {
            $this->separator = $this->params['separator'];
        }
        if (isset($this->params['value'])) {
            $this->value = $this->params['value'];
        }
        if (isset($this->params['conditions'])) {
            $this->conditions = $this->params['conditions'];
        }
        if (isset($this->params['required'])) {
            $this->required = $this->params['required'];
        }

        //Conditions pegado con required y subtype
        $conditions = array();
        if ($this->conditions != '') {
            $conditions = explode('|', $this->conditions);
        }
        if ($this->required) {
            $conditions[] = 'required';
        }
        if ($this->subtype != '') {
            $conditions[] = $this->subtype;
        }
        $this->conditions = implode('|', $conditions);

        //LABEL
        $label = '';
        if (isset($this->params['label'])) {
            $label = $this->params['label'];
        } else {
            $label = \humanize(str_replace('id_', '', $this->name));
        }

        if ($this->mode === 'form_edit' || $this->mode === 'form_static') {
            $this->label = '<label class="control-label" for="' . $this->name . '">' . $label . '</label>';
        } elseif ($this->mode === 'table_static' || $this->mode === 'table_edit') {
            $this->label = $label;
        }
    }

    public function _display ($valor, $other_values = array())
    {
        return $valor;
    }

    public function _install ()
    {
        return '';
    }

    public function _insert ($valor)
    {
        return $valor;
    }

    public function _update ($valor)
    {
        return $valor;
    }

    public function _delete ($valor)
    {
        return $valor;
    }

    public function _get_column_information ()
    {
        return array();
    }

    public function _after_add_data_query ($params)
    {
        return;
    }

    public function _get_value ($valor = '')
    {
        return $valor;
    }
}
