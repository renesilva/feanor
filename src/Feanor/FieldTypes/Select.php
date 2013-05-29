<?php
/**
 *
 * Genera un <select> que puede tener datos ya seleccionados.
 *
 * @package    Feanor/FieldTypes
 * @author     Rene Silva <rsilva@eresseasolutions.com>
 * @copyright  Copyright (c) 2013 Eressea Solutions Development Team
 * @license    MIT License
 */
namespace Feanor\FieldTypes;

use Feanor\DB;

class Select extends \Feanor\FieldType
{
    //Acá se guardarán las opciones recuperadas de la base de datos
    protected $options;
    //campos elegidos de la base de datos además del campo id_NombreDeTabla
    protected $fields;
    //nombre de la tabla
    protected $foreign_table;
    //a quien se refiere en la otra tabla
    protected $references;
    //Order
    protected $order;
    //limit
    protected $limit;
    //boolean
    protected $primary_key = false;
    protected $additional_tables = array();
    protected $additional_conditions = array();
    public $options_cache = array();

    public function __construct ($_params = null, $_mode = null)
    {

        parent::__construct($_params, $_mode);
        if (isset($this->params['value'])) {
            if (isset($this->params['save_as_text']) && $this->params['save_as_text']) {
                $this->value = explode('|', $this->params['value']);
            } else {
                $this->value = $this->params['value'];
            }
        } else {
            $this->value = 0;
        }

        if (isset($this->params['primary_key'])) {
            $this->primary_key = $this->params['primary_key'];
        }

        isset($this->params['fields']) ? $this->fields = $this->params['fields'] : $this->fields = '';
        isset($this->params['foreign_table']) ?
                        $this->foreign_table = $this->params['foreign_table'] : $this->foreign_table = '';

        if (isset($this->params['options'])) {
            if (is_array($this->params['options'])) {
                $this->options = $this->params['options'];
            } elseif (is_object($this->params['options'])) {
                //si es objeto?
                $this->options = (array) $this->params['options'];
            }
        } else {
            $this->options = array();
        }

        isset($this->params['references']) ?
                        $this->references = $this->params['references'] :
                        $this->references = 'id_' . $this->foreign_table;
        isset($this->params['order']) ? $this->order = $this->params['order'] : $this->order = '';
        isset($this->params['limit']) ? $this->limit = $this->params['limit'] : $this->limit = '';
        isset($this->params['aditional_tables']) ?
                        $this->aditional_tables = $this->params['aditional_tables'] :
                        $this->aditional_tables = array();
        isset($this->params['aditional_conditions']) ?
                        $this->additional_conditions = $this->params['aditional_conditions'] :
                        $this->additional_conditions = array();
        if ($this->mode == 'form_static' || $this->mode == 'table_static') {
            //no importan las condiciones porque tenemos que mostrar el valor :S
            $this->additional_conditions = array();
            $this->additional_tables = array();
        }
    }

    public function display ($valor = null, $other_values = array())
    {
        $buffer = '';
        $options = $this->options;
        $additional_tables = $this->additional_tables;
        $additional_conditions = $this->additional_conditions;


        if ($valor !== null) {
            if (isset($this->params['save_as_text']) && $this->params['save_as_text']) {
                $valor = explode('|', $valor);
            }
        }


        $options_temp = $options;
        if (isset($this->params['first_element_empty']) && $this->params['first_element_empty']) {
            $options[0] = '';
        }

        if (empty($options_temp)) {

            //QUERIES!!!!
            if ($valor !== null && isset($this->options_cache[$valor]) && $this->mode === 'table_static') {
                //static
                $options[$valor] = $this->options_cache[$valor];
            } else {
                if (($this->mode === 'form_edit' || $this->mode === 'table_edit') && !empty($this->options_cache)) {
                    $options = $this->options_cache;
                } else {

                    $order = '';
                    $limit = '';
                    $conditions = '';
                    if ($this->mode == 'form_static' || $this->mode == 'table_static') {
                        if ($valor != 0) {
                            $conditions .= '`' . DB::dbprefix($this->foreign_table) . '`.`' .
                                    $this->references . '`=' . $valor;
                        }
                    } else {
                        //para uso de tablas externas.
                        //Por ejemplo en Joomla y su tabla de usuarios
                        if (isset($this->params['foreign_sw_condition'])) {
                            $conditions .= $this->params['foreign_sw_condition'];
                        } else {
                            //de la manera tabla.sw = 1
                            $conditions .= '`' . DB::dbprefix($this->foreign_table) . '`.sw = 1 ';
                        }
                        if ($this->order != '') {
                            $order = ' order by ' . $this->order;
                        }
                        if ($this->limit != '') {
                            $limit = ' limit ' . $this->limit;
                        }
                    }
                    if (sizeof($this->additional_conditions) > 0) {
                        $conditions = $conditions . ' and ' . implode(' and ', $this->additional_conditions);
                    }
                    //Si tiene algo parecido a "apellido|nombre" que son los campos que queremos mostrar en el
                    //select se guarda en un array.
                    $fieldsArray = explode("|", (string) $this->fields);
                    //adicionamos el nombre de la tabla a los campos
                    $fieldsArrayComp = array();
                    for ($i = 0; $i < sizeof($fieldsArray); $i++) {
                        $fieldsArrayComp[$i] = '`' . DB::dbprefix($this->foreign_table) .
                                '`.`' . $fieldsArray[$i] . '`';
                    }
                    //si no hay condiciones no hay where
                    if ($conditions != '') {
                        $conditions = 'where ' . $conditions;
                    }
                    //utilizamos aditional_tables como nuestras tablas
                    $additional_tables[] = DB::dbprefix($this->foreign_table);
                    $sql = 'SELECT ' . implode(',', $fieldsArrayComp) . ' ,
                        ' . DB::dbprefix($this->foreign_table) . '.' . $this->references;
                    $sql .= ' FROM ' . implode(',', $additional_tables) . ' ' . $conditions . $order . $limit;
                    $result = DB::query($sql);
                    if ($result->rowCount() > 0) {
                        $result_all = $result->fetchAll();
                        foreach ($result_all as $row) {
                            $id = $row[$this->references];
                            $options[$id] = '';
                            for ($jk = 0; $jk < sizeof($fieldsArray); $jk++) {
                                //para que muestre los campos elegidos "apellido nombre"
                                $options[$id] = $options[$id] . ' ' . $row[$fieldsArray[$jk]];
                            }
                            $this->options_cache[$id] = $options[$id];
                        }
                    }
                }
            }
        }

        if ($this->mode === 'form_edit' || $this->mode === 'form_static') {
            $buffer = $buffer . $this->label . '<div class="controls">';
        }


        if ($this->mode == 'form_static' || $this->mode == 'table_static') {
            if ($valor === null) {
                return "";
            }
            if (isset($this->params['save_as_text']) && $this->params['save_as_text']) {
                $buffer .= implode(', ', $valor);
            } else {
                //en caso de tener el parámetro "output" lo mostramos via eso
                if (isset($this->params['output'])) {
                    @$buffer .= sprintf($this->params['output'], $valor, $options[$valor]);
                } else {
                    $buffer .= $options[$valor];
                }
            }
        } elseif ($this->mode == 'form_edit' || $this->mode == 'table_edit') {
            if (isset($this->params['select_display_type'])) {
                if ($this->params['select_display_type'] == 'multiselect') {
                    $buffer .= form_multiselect($this->name, $options, $valor);
                } elseif ($this->params['select_display_type'] == 'radio') {
                    foreach ($options as $id_opcion => $opcion) {
                        $selected = '';
                        if ((!is_array($valor) && (string) $valor === (string) $id_opcion) ||
                                (is_array($valor) && in_array($id_opcion, $valor))) {
                            $selected = 'checked="checked"';
                        }
                        $buffer .= '
                            <input type="radio" name="' . $this->name . '" value="' .
                                $id_opcion . '" ' . $selected . '/>' . $opcion . '<br/>';
                    }
                } elseif ($this->params['select_display_type'] == 'checkbox') {
                    foreach ($options as $id_opcion => $opcion) {
                        $selected = '';
                        if ((!is_array($valor) && (string) $valor === (string) $id_opcion) ||
                                (is_array($valor) && in_array($id_opcion, $valor))) {
                            $selected = 'checked="checked"';
                        }
                        $buffer .= '
                            <input type="checkbox" name="' . $this->name . '[]"
                                value="' . $id_opcion . '" ' . $selected . '/>' . $opcion . '<br/>';
                    }
                }
            } else {
                $buffer .= '<select name=' . $this->name . '>';
                foreach ($options as $id_opcion => $opcion) {
                    $selected = '';
                    if ((!is_array($valor) && (string) $valor === (string) $id_opcion) ||
                            (is_array($valor) && in_array($id_opcion, $valor))) {
                        $selected = 'selected="selected"';
                    }
                    $buffer .= '<option value="' . $id_opcion . '" ' . $selected . '>' . $opcion . '</option>';
                }
                $buffer .= '</select>';
            }
        }

        if ($this->mode === 'form_edit' || $this->mode === 'form_static') {
            if (isset($this->params['help'])) {
                $buffer .= '<p class="help-block">' . $this->params['help'] . '</p>';
            }
            $buffer .= '</div>';
        }

        return $buffer;
    }

    public function install ()
    {

        if (isset($this->params['save_as_text']) && $this->params['save_as_text']) {
            $buffer = '';
            if (preg_match('/maxLength\[(.*?)\]/', $this->conditions, $val)) {
                $buffer = ' varchar (' . $val[1] . ') ';
            }
            if ($this->required) {
                $buffer .= ' not null ';
            } else {
                $buffer .= ' null ';
            }
            if (isset($this->params['default'])) {
                $buffer.=' default "' . $this->params['default'] . '" ';
            }
            return $this->name . ' ' . $buffer;
        } else {
            if (!$this->primary_key) {
                $notNull = '';
                if ($this->required) {
                    $notNull = ' not null ';
                } else {
                    $notNull = ' null ';
                }
                $tipo = ' INTEGER ' . $notNull . ',FOREIGN KEY(`' . $this->name . '`)
                            REFERENCES `' . DB::dbprefix($this->foreign_table) . '`(`' . $this->references . '`)';
                return '`' . $this->name . '` ' . $tipo;
            } else {
                $tipo = ' FOREIGN KEY(`' . $this->name . '`)
                                REFERENCES `' . DB::dbprefix($this->foreign_table) . '`(`' . $this->references . '`) ';
                return $tipo;
            }
        }
    }

    public function insert ($valor)
    {
        if (isset($this->params['save_as_text']) && $this->params['save_as_text'] && is_array($valor)) {
            $valor = implode('|', $valor);
        }
        return $valor;
    }

    public function update ($valor)
    {
        if (isset($this->params['save_as_text']) && $this->params['save_as_text'] && is_array($valor)) {
            $valor = implode('|', $valor);
        }
        return $valor;
    }

    public function delete ($valor)
    {
        return $valor;
    }
}
