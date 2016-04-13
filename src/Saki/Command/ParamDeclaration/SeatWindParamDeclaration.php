<?php

namespace Saki\Command\ParamDeclaration;

use Saki\Game\SeatWind;

class SeatWindParamDeclaration extends ParamDeclaration { // todo remove
//    function toObject() {
//        $paramString = $this->getParamString();
//
//        if ($paramString == 'I') {
//            return $this->getContext()->getAreas()->getCurrentSeatWind()->getWindTile();
//        }
//
//        $seatWind = Tile::fromString($paramString);
//        $valid = $seatWind->isWind();
//        if (!$valid) {
//            throw new \InvalidArgumentException();
//        }
//        return $seatWind;
//    }
    function toObject() {
        $paramString = $this->getParamString();

        if ($paramString == 'I') {
            return $this->getContext()->getCurrentSeatWind();
        }

        return SeatWind::fromString($paramString);
    }
}