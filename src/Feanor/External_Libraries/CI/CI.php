<?php
namespace Feanor\External_Libraries\CI;

class CI
{
    protected static $instance = null;
    public $lang;

    public function __construct ()
    {

    }

    public static function getInstance ()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static;
            static::$instance->lang = new CILang();
        }
        return static::$instance;
    }
}
