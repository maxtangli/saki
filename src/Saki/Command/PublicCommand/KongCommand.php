<?php
namespace Saki\Command\PublicCommand;

use Saki\Command\PrivateCommand;
use Saki\Command\PublicClaimCommand;
use Saki\Command\PublicCommand;
use Saki\Meld\QuadMeldType;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\ArrayList;

/**
 * @package Saki\Command\PublicCommand
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