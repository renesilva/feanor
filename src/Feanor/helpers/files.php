<?php

function diverseArray ($vector)
{
    $result = array();
    foreach ($vector as $key1 => $value1) {
        foreach ($value1 as $key2 => $value2) {
            $result[$key2][$key1] = $value2;
        }
    }
    return $result;
}
