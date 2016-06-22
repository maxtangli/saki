<?php
namespace Saki\Command\PublicCommand;

use Saki\Command\PrivateCommand;
use Saki\Command\PublicClaimCommand;
use Saki\Command\PublicCommand;
use Saki\Game\Area;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Meld\TripleMeldType;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\ArrayList;
use Saki\Util\Utils;

/**
 * @package Saki\Command\PublicCommand
 */
class PungCommand extends PublicClaimCommand {
    //region Command impl
    protected static function getExecutableListImpl(Round $round, SeatWind $actor, Area $actorArea) {
        $hand = $actorArea->getHand();
        $targetTile = $hand->getTarget()->getTile();
        $public = $hand->getPublic();

        $sameTileList = $public->getCopy()
            ->where(Utils::toPredicate($targetTile));
        if ($sameTileList->count() >= 2) {
            $toTileList = function (Tile $tile1, Tile $tile2) {
                return (new TileList([$tile1, $tile2]))
                    ->orderByTileID();
            };
            $equal = function (TileList $a, TileList $b) {
                $tileEqual = Tile::getEqual(true);
                return $tileEqual($a[0], $b[0]) && $tileEqual($a[1], $b[1]);
            };
            $toArray = function (TileList $list) {
                return [$list];
            };
            $otherParamsList = (new ArrayList())
                ->fromCombination($sameTileList, $toTileList)// handle red
                ->distinct($equal)
                ->select($toArray);
        } else {
            $otherParamsList = new ArrayList();
        }

        return static::createMany($round, $actor, $otherParamsList);
    }
    //endregion

    //region PublicClaimCommand impl
    function getClaimMeldType() {
        return TripleMeldType::create();
    }
    //endregion
}