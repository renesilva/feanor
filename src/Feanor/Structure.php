<?php
/**
 * Clase que define la Estructura que utilziamos en Cessil
 *
 * @package    Feanor
 * @author     Rene Silva <rsilva@eresseasolutions.com>
 * @copyright  Copyright (c) 2013 Eressea Solutions Development Team
 * @license    MIT License
 */
namespace Feanor;

class Structure
{
    /**
     * @var string Nombre de la tabla
     */
    public $table_name;

    /**
     * @var array Estructura definida por el usuario
     */
    public $structure;
    public $display_type;
    public $restriction = array();
    public $add_after_table_name = '';
    public $fields = array();
    public $select_column = array();

    public function __construct ($_structure, $_table_name = '', $_display_type = '', $_restriction = array())
    {
        $this->table_name = $_table_name;
        $this->structure = $_structure;
        $this->restriction = $_restriction;
        $this->display_type = $_display_type;

        foreach ($this->structure['fields'] as $fieldName => $field) {

            $displayTypeField = $this->display_type;

            if (isset($this->restriction[$fieldName])) {
                $displayTypeField = $this->restriction[$fieldName];
            }

            if ($displayTypeField != 'dont_show') { // && $fieldName != 'sw'
                if (isset($field['meta']) && $field['meta']) {
                    $field['dont_insert_in_query'] = true;
                }

                if (!(isset($field['dont_insert_in_query']) && $field['dont_insert_in_query'])) {
                    if (isset($field['field_in_query'])) {
                        // mi_propia_tabla.mi_campo as lo que quiera :)
                        $this->select_column[$fieldName] =
                                $field['field_in_query']['field'] . ' as ' . $field['field_in_query']['as'];
                    } else {
                        $this->select_column[$fieldName] = '`' . $this->table_name . '`.`' . $fieldName . '`';
                    }
                }
                if ((isset($field['add_after_table_name']))) {
                    $this->add_after_table_name.=" " . $field['add_after_table_name'];
                }

                $field['name'] = $fieldName;
                $field['display_type'] = $displayTypeField;
                $type_name = '\Feanor\FieldTypes\\' . $field['type'];
                $this->fields[$field['name']] = new $type_name($field, $this->display_type, $this);
            }
        }
    }
}
