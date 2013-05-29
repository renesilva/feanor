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
    private $rules;
    private $ar_path_info = array();

    public function __construct ()
    {

    }

    public function setController ($_controller)
    {
        $this->controller = $_controller;
    }

    public function setMethod ($_method)
    {
        $this->method = $_method;
    }

    public function getController ()
    {
        return $this->controller;
    }

    public function getMethod ()
    {
        return $this->method;
    }

    public static function register ($rule)
    {
        $this->rules[] = $rule;
    }

    public function init ()
    {
        if (isset($_SERVER['PATH_INFO'])) {
            //usamos rtrim para borrar el último "/"
            $path_info = rtrim($_SERVER['PATH_INFO'], '/');

            //cleaning de pathinfo ...
            //...

            $this->ar_path_info = explode('/', $path_info);
            unset($this->ar_path_info[0]);

            if (isset($this->ar_path_info[1]) && $this->ar_path_info[1] !== '') {
                $modules = Config::get('modules', array());
                if (in_array($this->ar_path_info[1], $modules)) {
                    FW::$loaded_module = $this->ar_path_info[1];
                }
            }
        }
    }

    public function execute ()
    {

        if (!empty($this->ar_path_info)) {

            if (isset($this->ar_path_info[1]) && $this->ar_path_info[1] !== '') {
                if (FW::$loaded_module != '') {
                    if (isset($this->ar_path_info[2]) && $this->ar_path_info[2] !== '') {
                        $this->setController(
                            '\\Modules\\' . ucfirst($this->ar_path_info[1]) . '\Controllers\\' .
                            ucfirst($this->ar_path_info[2])
                        );
                    } else {
                        $this->setController(Config::get('default_controller'));
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

        //verificamos si existe el controller de otra forma entregar un 404s
        $controller_name = $this->getController();
        $method_name = $this->getMethod();

        //set args
        $args = array_values($this->ar_path_info);

        try {
            if (class_exists($controller_name)) {
                $controller = new $controller_name();
                //le pasamos sus variables en caso de que las tenga
                call_user_func_array(array($controller, $method_name), $args);
            } else {
                throw new \Exception('No existe el controlador <strong>' . $controller_name . '</strong>');
            }
        } catch (\Exception $e) {
            echo 'Error: ' . $e->getMessage() . '<br/>';
            var_dump(debug_backtrace());
        }
    }
}
