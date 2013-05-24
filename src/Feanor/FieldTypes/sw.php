<?php
/**
 * Clase SW
 *
 * @package    Feanor/FieldTypes
 * @author     Rene Silva <rsilva@eresseasolutions.com>
 * @copyright  Copyright (c) 2013 Eressea Solutions Development Team
 * @license    MIT License
 */
namespace Feanor\FieldTypes;

class sw extends \Feanor\FieldType
{

    public function __construct ($_params = null, $_mode = null)
    {
        parent::__construct($_params, $_mode);
    }

    public function _install ()
    {
        return '`' . $this->name . '` TINYINT(1) not null default 1';
    }

    public function _display ($valor, $other_values = array())
    {
        return;
    }
}
