<?php
/**
 * Router
 *
 * @package    Feanor
 * @author     Rene Silva <rsilva@eresseasolutions.com>
 * @copyright  Copyright (c) 2013 Eressea Solutions Development Team
 * @license    MIT License
 */
namespace Feanor;

class Router
{
    private $controller;
    private $method;
    private $ar_path_info = array();

    public static $rules = array();
    
    public function __construct ()
    {

    }

    private function camelCase($string)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', str_replace('-', ' ', $string))));
    }

    public function setController ($_controller)
    {
        $this->controller = $this->camelCase($_controller);
    }

    public function setMethod ($_method)
    {
        $this->method = $this->camelCase($_method);
    }

    public function getController ()
    {
        return $this->controller;
    }

    public function getMethod ()
    {
        return $this->method;
    }

    public function getArPathInfo ()
    {
        return $this->ar_path_info;
    }

    /**
     *
     * @param string $rule
     * @param array $rule_info
     */
    public static function register ($rule, $rule_info)
    {
        static::$rules[$rule] = $rule_info;
    }

    public function init ()
    {
        if (isset($_SERVER['PATH_INFO'])) {

            //usamos rtrim para borrar el último "/"
            $path_info = rtrim($_SERVER['PATH_INFO'], '/');

            //cleaning de pathinfo ...
            //...

            //redirecciones
            $matches = array();
            foreach (static::$rules as $rule => $controller) {
                $final_rule = '/^'.str_replace('/', '\/', $rule).'$/';
                if (preg_match($final_rule, $path_info, $matches)) {
                    unset($matches[0]);//para que no muestre el mismo match :S
                    $path_info = $controller['route'].'/'.implode('/', $matches);
                    break;
                }
            }
            
            $this->ar_path_info = explode('/', $path_info);
            unset($this->ar_path_info[0]);

            if (isset($this->ar_path_info[1]) && $this->ar_path_info[1] !== '') {
                $modules = Config::get('modules', array());
                $this->ar_path_info[1] = ucfirst($this->ar_path_info[1]);
                if ($this->ar_path_info[1] === 'Core') {
                    FW::$loaded_module = 'Core';
                } elseif (in_array($this->ar_path_info[1], $modules)) {
                    FW::$loaded_module = $this->ar_path_info[1];
                }
            }
        }

        if (!empty($this->ar_path_info)) {

            if (isset($this->ar_path_info[1]) && $this->ar_path_info[1] !== '') {
                if (FW::$loaded_module != '') {

                    if (FW::$loaded_module === 'Core') {
                        $this->setController(
                            '\\Feanor\\' . ucfirst($this->ar_path_info[1]) . '\Controllers\\' .
                            ucfirst($this->ar_path_info[2])
                        );
                    } else {
                        if (isset($this->ar_path_info[2]) && $this->ar_path_info[2] !== '') {
                            $this->setController(
                                '\\Modules\\' . ucfirst($this->ar_path_info[1]) . '\Controllers\\' .
                                ucfirst($this->ar_path_info[2])
                            );
                        } else {
                            $this->setController(Config::get('default_controller'));
                        }
                    }


                    $new_ar_path_array = array();
                    foreach ($this->ar_path_info as $path_info_key => $path_info_element) {
                        $new_ar_path_array[$path_info_key - 1] = $path_info_element;
                    }
                    unset($new_ar_path_array[0]);
                    //el nuevo ar_path_info es lo mismo pero sin el primero valor de la carpeta
                    $this->ar_path_info = $new_ar_path_array;
                } else {
                    $this->setController('\Controllers\\' . ucfirst($this->ar_path_info[1]));
                }
                unset($this->ar_path_info[1]);
            }

            if (isset($this->ar_path_info[2]) && $this->ar_path_info[2] !== '') {
                $this->setMethod($this->ar_path_info[2]);
                unset($this->ar_path_info[2]);
            } else {
                $this->setMethod(Config::get('default_method'));
            }
            
        } else {
            $this->setController(Config::get('default_controller'));
            $this->setMethod(Config::get('default_method'));
        }
    }
}
