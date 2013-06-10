<?php
/**
 * Clase de utilidad para vaaarias cosas, entre ellas el control de la generación de Javascript
 *
 * @package    Feanor\Core\Utility
 * @author     Rene Silva <rsilva@eresseasolutions.com>
 * @copyright  Copyright (c) 2013 Eressea Solutions Development Team
 * @license    MIT License
 */
namespace Feanor\Core\Controllers;

use Feanor\Controller;
use Feanor\Config;
use Feanor\View;

class Utility extends Controller
{

    public function __construct ()
    {

    }

    public function allJS ($clear_cache = '')
    {
        if ($clear_cache == '') {
            echo $this->generateJS(Config::get('js_params_all'));
        }
    }

    /**
     * Funcion Generate JS ABM's
     * @param Array $array Nombres con los que se desa hacer el ABM
     */
    private function generateJS ($array = null)
    {
        if ($array !== null) {
            //$match = array();
            $function_name = '';
            $position = '';
            $fp = fopen(BASEPATH . 'app/Config/config_javascript.js', 'r');
            while (($line = fgets($fp)) !== false) {
                $match = array();
                if (preg_match('/(\[)(?P<function_name>\w+)_(?P<position>\w+)(\]\*\/)/', $line, $match)) {
                    $function_name = $match['function_name'];
                    $position = $match['position'];
                }
                if ($function_name != '' && $position != '' && isset($array[$function_name])) {
                    if (!isset($array[$function_name][$position])) {
                        $array[$function_name][$position] = '';
                    }
                    $array[$function_name][$position].=$line;
                }
            }
            $buffer = '';

            View::set('functions', $array);
            View::$layout = 'Core/Views/generatejs.php';
            $buffer .= View::render(true);
            //mostramos el BAse_url
            header("Content-type: application/x-javascript");
            echo '
	base_url="' . BASE_URL . '";
	';
            return $buffer;
        } else {
            return;
        }
    }
}
