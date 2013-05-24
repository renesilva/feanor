<?php

class CI
{
    protected static $instance = null;
    public $lang;

    public function __construct ()
    {

    }

    public static function get_instance ()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static;
            static::$instance->lang = new CI_Lang();
        }
        return static::$instance;
    }
}

function get_ci_instance ()
{
    return CI::get_instance();
}
