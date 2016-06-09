<?php

namespace Saki\Command\ParamDeclaration;

/**
 * @package Saki\Command\ParamDeclaration
 */

class IntParamDeclaration extends ParamDeclaration {
    //region ParamDeclaration impl
    function toObject() {
        $paramString = $this->getParamString();
        $integer = intval($paramString);
        return $integer;
    }
    //endregion
}