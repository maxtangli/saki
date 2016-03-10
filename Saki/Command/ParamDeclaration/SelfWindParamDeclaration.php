<?php

namespace Saki\Command\ParamDeclaration;

use Saki\Tile\Tile;

class SelfWindParamDeclaration extends ParamDeclaration {
    function toObject() {
        $paramString = $this->getParamString();

        if ($paramString == 'I') {
            $currentPlayer = $this->getContext()->getRoundData()->getTurnManager()->getCurrentPlayer();
            return $currentPlayer->getSelfWind();
        }

        $selfWind = Tile::fromString($paramString);
        $valid = $selfWind->isWind();
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        return $selfWind;
    }
}