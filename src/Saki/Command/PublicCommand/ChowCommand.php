<?php
namespace Saki\Command\PublicCommand;

use Saki\Command\PrivateCommand;
use Saki\Command\PublicClaimCommand;
use Saki\Command\PublicCommand;
use Saki\Game\Area;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Meld\RunMeldType;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\ArrayList;

/**
 * @package Saki\Command\PublicCommand
 */
class ChowCommand extends PublicClaimCommand {
    //region Command impl
    protected static function getExecutableListImpl(Round $round, SeatWind $actor, Area $actorArea) {
        $hand = $actorArea->getHand();
        $targetTile = $hand->getTarget()->getTile();
        $public = $hand->getPublic();

        if (!$targetTile->isSuit()) {
            return new ArrayList();
        }

        $mayChow = function (Tile $tile) use ($targetTile) {
            $number = $targetTile->getNumber();
            $numberRange = range($number - 2, $number + 2);
            return $tile->getTileType() == $targetTile->getTileType()
            && in_array($tile->getNumber(), $numberRange);
        };
        $mayChowTileList = $public->getCopy()
            ->where($mayChow);

        $toTileList = function (Tile $tile1, Tile $tile2) {
            return (new TileList([$tile1, $tile2]))->orderByTileID();
        };
        $equal = function (TileList $a, TileList $b) {
            $tileEqual = Tile::getEqual(true);
            return $tileEqual($a[0], $b[0]) && $tileEqual($a[1], $b[1]);
        };
        $validChow = function (TileList $tileList) use ($targetTile) {
            $tileList = $tileList->getCopy()->insertLast($targetTile);
            return RunMeldType::create()->valid($tileList);
        };
        $toArray = function (TileList $list) {
            return [$list];
        };
        $otherParamsList = (new ArrayList())
            ->fromCombination($mayChowTileList, $toTileList)
            ->distinct($equal)
            ->where($validChow)
            ->select($toArray);

        return static::createMany($round, $actor, $otherParamsList);
    }
    //endregion

    //region PublicClaimCommand impl
    function getClaimMeldType() {
        return RunMeldType::create();
    }
    //endregion
}