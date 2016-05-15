<?php
namespace Saki\Command\ParamDeclaration;

use Saki\Tile\Tile;
use Saki\Tile\TileList;

class TileParamDeclaration extends ParamDeclaration {
    function toObject() {
        $round = $this->getContext()->getRound();
        $paramString = $this->getParamString();

        /**
         * first part
         * [ESWN]:       player's
         *
         * second part
         * s-#TileList#: replace player's hand by tile
         *
         * third part
         * #Tile#        actual tile
         */
        $matches = [];
        if (preg_match('/([ESWNI]):(.+)/', $paramString, $matches)) {
            list(, $seatWindString, $remainParamString) = $matches;

            $seatWind = (new SeatWindParamDeclaration($this->getContext(), $seatWindString))->toObject();
            if (preg_match('/s-(.+):(.+)/', $remainParamString, $matches)) {
                list(, $mockTileListString, $tileString) = $matches;
                $tileList = TileList::fromString($mockTileListString);

                $area = $round->getAreas()->getArea($seatWind);
                $area->setHand($area->getHand()->toMockHand($tileList));

                $tile = Tile::fromString($tileString); // validate
                return $tile;
            }
        }

        return Tile::fromString($paramString);
    }
}