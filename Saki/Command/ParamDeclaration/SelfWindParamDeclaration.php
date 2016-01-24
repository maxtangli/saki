<?php

namespace Saki\Command\ParamDeclaration;

use Saki\Tile\Tile;

class SelfWindParamDeclaration extends ParamDeclaration {
    function toObject() {
        $selfWind = Tile::fromString($this->getParamString());
        $valid = $selfWind->isWind();
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        return $selfWind;
    }
}