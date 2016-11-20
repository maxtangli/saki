<?php
namespace Saki\Command\PublicCommand;

use Saki\Game\Meld\QuadMeldType;
use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Util\ArrayList;

/**
 * @package Saki\Command\PublicCommand\PublicCommand
 */
class KongCommand extends PublicClaimCommand {
    //region PublicClaimCommand impl
    function getClaimMeldType() {
        return QuadMeldType::create();
    }

    protected static function getOtherParamsListImpl(Tile $notRedTargetTile) {
        $notRedParams = new TileList([$notRedTargetTile, $notRedTargetTile, $notRedTargetTile]);
        $otherParamsList = new ArrayList([$notRedParams]);

        if ($notRedTargetTile->ableToRed()) {
            $redParams = new TileList([$notRedTargetTile, $notRedTargetTile, $notRedTargetTile->toRed()]);
            $otherParamsList->insertLast($redParams);
        }

        return $otherParamsList;
    }
    //endregion
}