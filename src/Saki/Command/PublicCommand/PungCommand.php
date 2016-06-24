<?php
namespace Saki\Command\PublicCommand;

use Saki\Command\PrivateCommand;
use Saki\Command\PublicClaimCommand;
use Saki\Command\PublicCommand;
use Saki\Meld\TripleMeldType;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\ArrayList;

/**
 * @package Saki\Command\PublicCommand
 */
class PungCommand extends PublicClaimCommand {
    //region PublicClaimCommand impl
    function getClaimMeldType() {
        return TripleMeldType::create();
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