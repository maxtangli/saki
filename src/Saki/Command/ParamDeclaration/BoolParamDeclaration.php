<?php

namespace Saki\Command\ParamDeclaration;

/**
 * @package Saki\Command\ParamDeclaration
 */

class BoolParamDeclaration extends ParamDeclaration {
    //region ParamDeclaration impl
    function toObject() {
        $paramString = $this->getParamString();
        $map = ['true' => true, 'false' => false];
        if (!isset($map[$paramString])) {
            throw new \InvalidArgumentException(
                "Invalid \$paramString[$paramString]."
            );
        }
        $bool = $map[$paramString];
        return $bool;
    }
    //endregion
}