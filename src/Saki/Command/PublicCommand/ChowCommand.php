<?php
namespace Saki\Command\PublicCommand;

use Saki\Command\PublicClaimCommand;
use Saki\Game\Meld\RunMeldType;
use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Util\ArrayList;
use Saki\Util\Utils;

/**
 * @package Saki\Command\PublicCommand
 */
class ChowCommand extends PublicClaimCommand {
    //region PublicClaimCommand impl
    function requirePublicNextActor() {
        return true;
    }

    function getClaimMeldType() {
        return RunMeldType::create();
    }

    protected static function getOtherParamsListImpl(Tile $notRedTargetTile) {
        if (!$notRedTargetTile->isSuit()) {
            return new ArrayList();
        }

        /**
         * 1  :                 [+1,+2]
         * 2,8:         [-1,+1]
         * 3-7: [-2,-1],[-1,+1],[+1,+2]
         */
        $tileType = $notRedTargetTile->getTileType();
        $number = $notRedTargetTile->getNumber();

        $offsets = [];
        if (Utils::inRange($number, 3, 7)) {
            $offsets[] = [$number - 2, $number - 1];
        }
        if ($number != 1) {
            $offsets[] = [$number - 1, $number + 1];
        }
        if ($number != 2 && $number != 8) {
            $offsets[] = [$number + 1, $number + 2];
        }

        $toTileLists = function (array $numbers) use ($tileType) {
            $tileList = TileList::fromNumbers($numbers, $tileType);
            $results = [$tileList];

            /** @var Tile $tile */
            foreach ($tileList as $index => $tile) {
                if ($tile->ableToRed()) {
                    $redConsidered = $tileList->getCopy()
                        ->replaceAt($index, $tile->toRed());
                    $results[] = $redConsidered;
                }
            }
            return $results;
        };
        $otherParamsList = (new ArrayList())
            ->fromSelectMany(new ArrayList($offsets), $toTileLists);
        return $otherParamsList;
    }
    //endregion
}