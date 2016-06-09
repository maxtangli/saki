<?php
namespace Saki\Game;

use Saki\Tile\Tile;

/**
 * @package Saki\Game
 */
class Riichi extends Open {
    //region override Open
    function __construct(SeatWind $actor, Tile $tile) {
        parent::__construct($actor, $tile, true);
    }

    function valid(Area $area) {
        $waitingAnalyzer = $area->getRound()->getGameData()
            ->getWinAnalyzer()->getWaitingAnalyzer();
        $hand = $area->getHand();
        list($private, $melded, $tile) = [$hand->getPrivate(), $hand->getMelded(), $this->getTile()];

        return parent::valid($area)
        && $area->getHand()->isConcealed()
        && !$area->getRiichiStatus()->isRiichi()
        && $area->getPoint() >= 1000
        && $area->getRound()->getWall()->getRemainTileCount() >= 4
        && $waitingAnalyzer->isWaitingAfterDiscard($private, $melded, $tile); // slowest logic last
    }

    function apply(Area $area) {
        parent::apply($area);

        $round = $area->getRound();
        $riichiStatus = new RiichiStatus($round->getTurn());
        $round->getRiichiHolder()
            ->setRiichiStatus($area->getSeatWind(), $riichiStatus);
        $round->getPointHolder()
            ->setPointChange($this->getActor(), -1000);
    }
    //endregion
}