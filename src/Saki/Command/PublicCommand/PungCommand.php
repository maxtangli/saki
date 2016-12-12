<?php
namespace Saki\Command\PublicCommand;

use Saki\Game\Meld\PungMeldType;
use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Util\ArrayList;

/**
 * @package Saki\Command\PublicCommand\PublicCommand
 */
class PungCommand extends PublicClaimCommand {
    //region PublicClaimCommand impl
    function getClaimMeldType() {
        return PungMeldType::create();
    }

    protected static function getOtherParamsListImpl(Tile $notRedTargetTile) {
        $notRedParams = new TileList([$notRedTargetTile, $notRedTargetTile]);
        $otherParamsList = new ArrayList([$notRedParams]);

        if ($notRedTargetTile->ableToRed()) {
            $redParams = new TileList([$notRedTargetTile, $notRedTargetTile->toRed()]);
            $otherParamsList->insertLast($redParams);
        }

        return $otherParamsList;
    }
    //endregion
}