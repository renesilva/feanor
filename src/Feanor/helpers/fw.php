<?php
namespace Feanor;

/**
 * Get metadata
 * @param string $key meta_key que se quiere obtener
 * @param string $meta_object_name nombre del objeto meta
 * @param integer $id ID del objeto
 * @param string $default_value en caso de que no haya nada :S
 * @param string $meta_tablename nombre de otra tabla JUST IN CASE
 * @return string
 */
function get_metadata ($key, $meta_object_name, $id, $default_value = '', $meta_tablename = '')
{

    $id_name = 'id_' . $meta_object_name;

    if ($meta_tablename == '') {
        $meta_tablename = $meta_object_name . '_meta';
    }

    $value = $default_value;

    $sql = 'SELECT * FROM `' . $meta_tablename . '` WHERE `meta_key`=? AND `sw`=1 AND `' . $id_name . '`=?';

    $result = DB::prepare($sql);
    $result->execute(array($key, $id));

    if ($result->rowCount() > 0) {
        $row = $result->fetch();
        $value = $row['meta_value'];
    }

    return $value;
}

/**
 * Save metadata
 * @param string $key
 * @param string $value
 * @param string $meta_object_name For example 'user'
 * @param integer $id
 * @param intger $autoload 1 si - 0 no
 * @param string $meta_tablename just in case
 * @return boolean
 */
function save_metadata ($key, $value, $meta_object_name, $id, $autoload = 0, $meta_tablename = '')
{

    $id_name = 'id_' . $meta_object_name;

    if ($meta_tablename == '') {
        $meta_tablename = $meta_object_name . '_meta';
    }

    $success = false;

    $sql_select = 'SELECT * FROM `' . $meta_tablename . '` WHERE `meta_key`=? AND `sw`=1 AND `' . $id_name . '`=?';

    $result_select = DB::prepare($sql_select);
    $result_select->execute(array($key, $id));

    if ($result_select->rowCount() > 0) {
        //ya existe entonces update
        $sql = 'UPDATE `' . $meta_tablename . '` SET `meta_value` = ?, `autoload` = ?
                    WHERE `' . $id_name . '` = ? AND sw = 1 AND `meta_key` = ?';
        $result = DB::prepare($sql);
        $result->execute(array($value, $autoload, $id, $key));
    } else {
        //no existe INSERT!!!
        $sql = 'INSERT INTO `' . $meta_tablename . '` VALUES (NULL,?,?,?,?,1)';
        $result = DB::prepare($sql);
        $result->execute(array($id, $key, $value, $autoload));
    }

    if ($result->rowCount() >= 1) {
        $success = true;
    }

    return $success;
}
