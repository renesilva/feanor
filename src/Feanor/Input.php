<?php
/**
 * Input library
 *
 * @package    Feanor
 * @author     Rene Silva <rsilva@eresseasolutions.com>
 * @copyright  Copyright (c) 2013 Eressea Solutions Development Team
 * @license    MIT License
 */
namespace Feanor;

class Input
{

    public function __construct ()
    {

    }

    /**
     * Función que entrega del $_GET pero ya limpiado
     * Si es que no tiene ni variable ni default entonces entrega el array completo
     *
     * @param string $variable
     * @param string $default valor por defecto en caso de que no exista
     * @param boolean $clean_xss En true, usar con mucho cuidado :S
     * @return string
     */
    public static function get ($variable = '', $default = '', $clean_xss = true)
    {
        if ($variable === '') {
            return array_map(
                    function($value) {
                        global $clean_xss;
                        if ($clean_xss) {
                            return self::clean($value);
                        } else {
                            return $value;
                        }
                    }, $_GET
            );
        } else {
            $return_variable = $default;
            if (isset($_GET[$variable])) {
                if ($clean_xss) {
                    $return_variable = self::clean($_GET[$variable]);
                } else {
                    $return_variable = $_GET[$variable];
                }
            }
            return $return_variable;
        }
    }

    /**
     * Función que entrega del $_POST pero ya limpiado
     * Si es que no tiene ni variable ni default entonces entrega el array completo
     *
     * @param string $variable
     * @param string $default valor por defecto en caso de que no exista
     * @param boolean $clean_xss En true, usar con mucho cuidado :S
     * @return string
     */
    public static function post ($variable = '', $default = '', $clean_xss = true)
    {
        if ($variable === '') {
            return array_map(
                    function($value) {
                        global $clean_xss;
                        if ($clean_xss) {
                            return self::clean($value);
                        } else {
                            return $value;
                        }
                    }, $_POST
            );
        } else {
            $return_variable = $default;
            if (isset($_POST[$variable])) {
                if ($clean_xss) {
                    $return_variable = self::clean($_POST[$variable]);
                } else {
                    $return_variable = $_POST[$variable];
                }
            }
            return $return_variable;
        }
    }

    /**
     * Función que entrega del $_COOKIE pero ya limpiado
     * Si es que no tiene ni variable ni default entonces entrega el array completo
     *
     * @param string $variable
     * @param mixed $default valor por defecto en caso de que no exista
     * @return string|array
     */
    public static function cookie ($variable = '', $default = '')
    {
        if ($variable === '') {
            return array_map(
                    function($value) {
                        return self::clean($value);
                    }, $_COOKIE
            );
        } else {
            $return_variable = $default;
            if (isset($_COOKIE[$variable])) {
                $return_variable = self::clean($_COOKIE[$variable]);
            }
            return $return_variable;
        }
    }

    /**
     * Valor para set las cookies
     *
     * @param string $name   nombre de la cookie
     * @param string $value   valor de la cookie
     * @param integer $expiration vida en segundos
     * @param string $path  path de la cookie
     * @param string $domain  dominio del a cookie
     * @param boolean $secure  para que solo funcione sobre HTTPS
     * @param boolean $http_only para que solo funcione desde HTTP
     * @return boolean
     */
    public static function set_cookie (
    $name, $value, $expiration = 0, $path = null, $domain = null, $secure = false, $http_only = false)
    {

        if ($expiration != 0) {
            $expiration = time() + $expiration;
        }
        if (is_null($path)) {
            $path = BASE_PATH;
        }
        if (is_null($domain)) {
            $domain = BASE_DOMAIN;
        }
        return setcookie($name, $value, $expiration, $path, $domain, $secure, $http_only);
    }

    /**
     * Para borrar cookies
     *
     * @param string $name
     * @return boolean
     */
    public static function delete_cookie ($name)
    {
        unset($_COOKIE[$name]);
        return static::set_cookie($name, null, -86400);
    }

    private static function clean ($str)
    {
        //utilizando la clase Security de Codeigniter
        return FW::$security->xss_clean($str);
        ;
    }
}
