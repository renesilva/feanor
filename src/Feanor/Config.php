<?php
/**
 * Clase de configuración, con funciones get y set
 *
 * @package    Feanor
 * @author     Rene Silva <rsilva@eresseasolutions.com>
 * @copyright  Copyright (c) 2013 Eressea Solutions Development Team
 * @license    MIT License
 */
namespace Feanor;

class Config
{
    /**
     * Array de configuración
     * @var array
     */
    public static $confArray;

    /**
     * Obteniendo la configuración, por defecto se utiliza la configuración global
     * pero si se está dentro de un módulo entonces se utiliza la configuración
     * del módulo.
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function get ($name, $default = '')
    {

        if (isset(self::$confArray[$name])) {
            $value = self::$confArray[$name];
            if (FW::$loaded_module != '') {
                if (isset(self::$confArray['modules_config'][FW::$loaded_module][$name])) {
                    $value = self::$confArray['modules_config'][FW::$loaded_module][$name];
                }
            }
            return $value;
        } else {
            return $default;
        }

    }

    /**
     * Setting los valores, si se desea se coloca el valor para el módulo
     * @param string $name
     * @param mixed $value
     * @param string $module
     */
    public static function set ($name, $value, $module = '')
    {
        self::$confArray[$name] = $value;
        if ($module != '') {
            self::$confArray['modules_config'][$module][$name] = $value;
        }
    }
}
