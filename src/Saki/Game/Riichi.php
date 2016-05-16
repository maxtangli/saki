<?php
namespace Saki\Game;

use Saki\Tile\Tile;

class Riichi extends Open {
    function __construct(SeatWind $actor, Tile $tile) {
        parent::__construct($actor, $tile, true);
    }

    function valid(Area $area) {
        return parent::valid($area)
        && $area->getHand()->isConcealed()
        && !$area->getRiichiStatus()->isRiichi()
        && $area->getPoint() >= 1000
        && $area->getAreas()->getWall()->getRemainTileCount() >= 4;
    }

    function apply(Area $area) {
        parent::apply($area);

        $areas = $area->getAreas();
        $riichiStatus = new RiichiStatus($areas->getTurn());
        $areas->getRiichiHolder()
            ->setRiichiStatus($area->getSeatWind(), $riichiStatus);
        $areas->getPointHolder()
            ->setPointChange($this->getActor(), -1000);
    }
}