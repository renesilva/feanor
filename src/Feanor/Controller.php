<?php
/**
 * Clase base para los controladores
 *
 * @package    Feanor
 * @author     Rene Silva <rsilva@eresseasolutions.com>
 * @copyright  Copyright (c) 2013 Eressea Solutions Development Team
 * @license    MIT License
 */
namespace Feanor;

class Controller
{
    /**
     * En caso de que no exista nos entrega un error
     * @param string $func
     * @param mixed $args
     * @throws \Exception
     */
    public function __call ($func, $args)
    {
        if (!method_exists($this, $func)) {
            throw new \Exception(
                'No existe el m&eacute;todo <strong>' . $func . '</strong>
                 en el controlador <strong>' . get_class($this) . '</strong>'
            );
        }
    }
}
