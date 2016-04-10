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
            list(, $selfWindString, $remainParamString) = $matches;

            $selfWind = (new SelfWindParamDeclaration($this->getContext(), $selfWindString))->toObject();

            $player = $round->getPlayerList()->getSelfWindPlayer($selfWind);
            $areas = $round->getAreas();

            if (preg_match('/s-(.+):(.+)/', $remainParamString, $matches)) {
                list(, $mockTileListString, $tileString) = $matches;
                $tileList = TileList::fromString($mockTileListString);

                $areas->debugMockHand($player, $tileList);

                $tile = Tile::fromString($tileString); // validate
                return $tile;
            }
        }

        return Tile::fromString($paramString);
    }
}