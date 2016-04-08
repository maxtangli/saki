<?php

namespace Saki\Command\ParamDeclaration;

use Saki\Tile\Tile;

class SelfWindParamDeclaration extends ParamDeclaration {
    function toObject() {
        $paramString = $this->getParamString();

        if ($paramString == 'I') {
            $currentPlayer = $this->getContext()->getRound()->getTurnManager()->getCurrentPlayer();
            return $currentPlayer->getTileArea()->getPlayerWind()->getWindTile();
        }

        $selfWind = Tile::fromString($paramString);
        $valid = $selfWind->isWind();
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        return $selfWind;
    }
}