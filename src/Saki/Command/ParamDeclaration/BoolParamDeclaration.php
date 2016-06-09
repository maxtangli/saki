<?php

namespace Saki\Command\ParamDeclaration;

/**
 * @package Saki\Command\ParamDeclaration
 */

class BoolParamDeclaration extends ParamDeclaration {
    //region ParamDeclaration impl
    function toObject() {
        $paramString = $this->getParamString();
        $bool = boolval($paramString);
        return $bool;
    }
    //endregion
}