<?php
/**
 * Created by PhpStorm.
 * User: reisen
 * Date: 4/1/2016
 * Time: 14:42 PM
 */
namespace Saki\Util;

trait ClassNameToString {
    function __toString() {
        // A\B\XXClass -> XXClass
        $actualClass = get_called_class();
        $lastSeparatorPos = strrpos($actualClass, '\\');
        return substr($actualClass, $lastSeparatorPos + 1);
    }
}