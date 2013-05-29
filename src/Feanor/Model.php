<?php
/**
 * Clase modeladora
 *
 * @package    Feanor
 * @author     Rene Silva <rsilva@eresseasolutions.com>
 * @copyright  Copyright (c) 2013 Eressea Solutions Development Team
 * @license    MIT License
 */
namespace Feanor;

class Model
{

    /**
     * Aceptamos un array o un integer :S
     *
     * @param Integer|Array $attributes_or_id
     * @throws \Exception En caso de que no exista el objeto o se encuentre más de un objeto
     */
    public function __construct ($attributes_or_id = array())
    {
        if (is_array($attributes_or_id)) {
            if (!empty($attributes_or_id)) {
                $where = array('where' => $attributes_or_id);
                $find = static::find($attributes_or_id, $where);
                if ($find == '') {
                    throw new \Exception('Objeto no encontrado');
                } else {
                    $this->set($find->get());
                }
            }
        } elseif (is_numeric($attributes_or_id)) {
            $find = static::find($attributes_or_id);
            if ($find == '') {
                throw new \Exception('Objeto no encontrado');
            } else {
                $this->set($find->get());
            }
            /* if($find->rowCount() == 1){
              $this->set($find->fetch());
              } elseif($find->rowCount() > 1) {
              throw new \Exception('Más de un objeto encontrado');
              } else {
              throw new \Exception('Objeto no encontrado');
              } */
        }
    }

    /**
     * Para guardar
     * @return mixed Si devuelve TRUE está todo ok, de otro modo devuelve un string
     */
    public function save ()
    {
        $success = false;
        $structure = static::getModelStructure();
        $key = $structure['keys']['key'][0];
        $add_data = \Feanor\FW::addData($structure, $this->get(), array(), $this->$key);
        if ($add_data['errors'] === false) {
            $this->$key = $add_data['id'];
            $success = true;
        } else {
            $success = $add_data['errors'];
        }
        return $success;
    }

    /**
     * Situaciones
     *
     * find(1)
     * find(array('id_user'=>1,'id_encuesta'=>1)) //TODO implementar esta
     * find("all")
     * find("first")
     * find("last")
     * find("all",	array( 'fields'=> array('id_encuesta','titulo') )//where
     * find("all",	array( 'where'=> array('id_encuesta'=>1) )//where
     * find("all",	array( 'order_by'=>array('titulo','DESC') )//where
     * find("all",	array( 'limit'=>array(0,15) )//where
     *
     * @param mixed $id_or_option
     * @param array $options
     * @return mixed
     */
    public static function find ($id_or_option, $options = array())
    {

        $values = array();
        $fields = array('*');
        $where = '';
        $order_by = '';
        $limit = '';

        $plural = false;

        if (is_numeric($id_or_option)) {
            $options['where'] = array('id_' . static::getModelName() => $id_or_option);
        } elseif (is_array($id_or_option)) {
            //2 primary keys :S
            //TODO implementar la opción para que podamos utilizar 2 primary keys :S
        } elseif (is_string($id_or_option)) {
            switch ($id_or_option) {
                case 'all':
                    $plural = true;
                    break;
                case 'first':
                    break;
                case 'last':
                    break;
            }
        }
        if (!empty($options)) {
            //fields
            if (isset($options['fields'])) {
                $fields = $options['fields'];
            }
            //where
            if (isset($options['where'])) {
                $where_array = array();
                foreach ($options['where'] as $key_where => $val_where) {
                    if (is_numeric($key_where)) {
                        $where_array[] = $val_where;
                    } else {
                        $where_array[] = $key_where . '=:' . $key_where;
                        $values[$key_where] = $val_where;
                    }
                }
                $where = ' WHERE ' . implode(' AND ', $where_array);
            }
            //order_by
            if (isset($options['order_by'])) {
                $order = 'ASC';
                if (isset($options['order_by'][1])) {
                    $order = $options['order_by'][1];
                }
                $order_by = ' ORDER BY ' . $options['order_by'][0] . ' ' . $order;
            }
            //limit
            if (isset($options['limit'])) {
                $limit = ' LIMIT ' . implode(',', $options['limit']);
            }
        }

        $query = '
        SELECT ' . implode(',', $fields) . '
        FROM ' . DB::$dbprefix_str . static::getModelTableName()
                . $where
                . $order_by
                . $limit;
        try {
            $result = DB::prepare($query);
            $class_name = get_called_class();
            $result->setFetchMode(\PDO::FETCH_CLASS, $class_name);
            $result->execute($values);
            if ($plural) {
                return $result->fetchAll();
            } else {
                return $result->fetch();
            }
        } catch (\Exception $e) {
            echo $e->getMessage() . '<br/>
                <strong>Model:</strong> ' . get_called_class() . '<br/>
                <strong>Query:</strong> ' . $query . '<br/>
                <strong>Options:</strong> ' . var_export($options, true) . '<br/>
                <strong>Values:</strong> ' . var_export($values, true) . '<br/>
                ';
            var_dump(debug_backtrace());
        }
    }

    /**
     * Get them all!
     * @param array $options
     * @return array
     */
    public static function findAll ($options = array())
    {
        return static::find('all', $options);
    }

    /**
     * Colocamos los elementos del array como propiedades
     *
     * @param array $attributes
     */
    public function set ($attributes = array())
    {
        foreach ($attributes as $key => $val) {
            if (property_exists($this, $key)) {
                $this->$key = $val;
            }
        }
    }

    /**
     * Colocamos los elementos del array como propiedades
     *
     * @param array $attributes
     */
    public function get ()
    {
        return get_object_vars($this);
    }
    /*     * ***************************************************
     * Funciones estáticas para los hijos
     * ************************************************** */

    /**
     * Funcion para obteener el nombre del model y guardarlo en modelo info
     *
     * Antes (osea hace unos minutos), utilizaba get_called_class pero static:: hace lo mismo :S
     * @return string
     */
    public static function getModelName ()
    {
        if (!isset(static::$model_info['model_name'])) {
            $class = get_called_class();
            $class_name = explode('\\', $class);
            static::$model_info['model_name'] = strtolower(end($class_name));
        }
        return static::$model_info['model_name'];
    }

    /**
     * Funcion para definir el nombre de la tabla que utilizaremos
     *
     * @return string
     */
    public static function getModelTableName ()
    {
        if (!isset(static::$model_info['table_name'])) {
            $class = get_called_class();
            $class_name = explode('\\', $class);
            static::$model_info['table_name'] = strtolower(end($class_name));
        }
        return static::$model_info['table_name'];
    }

    /**
     * Función que obtiene el modelo desde Reflection!
     *
     * @return Model
     */
    public static function getModelStructure ()
    {
        if (!isset(static::$model_info['model_structure'])) {
            $fields = array();
            $keys = array();
            $matches = array();
            $auto_increment = false;

            $my_class = new \ReflectionClass(get_called_class());
            $properties = $my_class->getProperties();

            foreach ($properties as $property) {
                $doc_info_property = $property->getDocComment();
                if (preg_match("/@(?P<type>\w+){/", $doc_info_property, $matches)) {
                    $pos = strpos($doc_info_property, '@' . $matches['type']);
                    $new_str = substr($doc_info_property, $pos);
                    $new_str = str_replace('@' . $matches['type'], '', $new_str);
                    $new_str = str_replace('*/', '', $new_str);
                    $new_str = trim($new_str);
                    if ($matches['type'] == 'Field') {
                        $fields[$property->name] = (array) json_decode($new_str);
                    } elseif ($matches['Type' == 'Key']) {
                        $json = (array) json_decode($new_str);
                        if (isset($json['auto_increment']) && $json['auto_increment']) {
                            $auto_increment = true;
                        }
                        $keys[] = $property->name;
                    }
                }
            }

            static::$model_info['model_structure'] = array(
                'fields' => $fields,
                'keys' => array(
                    'auto_increment' => $auto_increment,
                    'key' => $keys
                ),
                'table_name' => static::getModelTableName()
            );
        }
        return static::$model_info['model_structure'];
    }
}
