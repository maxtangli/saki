<?php

namespace Saki\Command\ParamDeclaration;

use Saki\Tile\Tile;

class SeatWindParamDeclaration extends ParamDeclaration {
    function toObject() {
        $paramString = $this->getParamString();

        if ($paramString == 'I') {
            $currentPlayer = $this->getContext()->getRound()->getTurnManager()->getCurrentPlayer();
            return $currentPlayer->getArea()->getSeatWind()->getWindTile();
        }

        $seatWind = Tile::fromString($paramString);
        $valid = $seatWind->isWind();
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        return $seatWind;
    }
}