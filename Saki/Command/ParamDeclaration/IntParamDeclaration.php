<?php

namespace Saki\Command\ParamDeclaration;

class IntParamDeclaration extends ParamDeclaration {
    function toObject() {
        $paramString = $this->getParamString();
        $integer = intval($paramString);
        return $integer;
    }
}