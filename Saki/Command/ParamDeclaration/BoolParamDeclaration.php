<?php

namespace Saki\Command\ParamDeclaration;

class BoolParamDeclaration extends ParamDeclaration {
    function toObject() {
        $paramString = $this->getParamString();
        $bool = boolval($paramString);
        return $bool;
    }
}