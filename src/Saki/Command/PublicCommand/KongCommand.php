<?php
namespace Saki\Command\PublicCommand;

use Saki\Command\PrivateCommand;
use Saki\Command\PublicClaimCommand;
use Saki\Command\PublicCommand;
use Saki\Game\Area;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Meld\QuadMeldType;
use Saki\Util\ArrayList;
use Saki\Util\Utils;

/**
 * @package Saki\Command\PublicCommand
 */
class KongCommand extends PublicClaimCommand {
    //region Command impl
    protected static function getExecutableListImpl(Round $round, SeatWind $actor, Area $actorArea) {
        $hand = $actorArea->getHand();
        $targetTile = $hand->getTarget()->getTile();
        $public = $hand->getPublic();

        $sameTileList = $public->getCopy()
            ->where(Utils::toPredicate($targetTile));
        if ($sameTileList->count() == 3) {
            $otherParams = [$sameTileList->orderByTileID()];
            $otherParamsList = new ArrayList([$otherParams]);
        } else {
            $otherParamsList = new ArrayList();
        }

        return static::createMany($round, $actor, $otherParamsList);
    }
    //endregion

    //region PublicClaimCommand impl
    function getClaimMeldType() {
        return QuadMeldType::create();
    }
    //endregion
}