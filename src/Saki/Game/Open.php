<?php
namespace Saki\Game;
use Saki\Tile\Tile;
use Saki\Util\Immutable;

//class Riichi extends Open {
//    function __construct(SeatWind $actor, Tile $tile) {
//        parent::__construct($actor, $tile, true);
//    }
//
//    function valid(Area $area) {
//        return parent::valid($area);
//    }
//
//    function apply(Area $area) {
//        parent::apply($area);
//    }
//}

/**
 * @package Saki\Game
 */
class Open implements Immutable {
    private $actor;
    private $tile;
    private $isDiscard;

    function __construct(SeatWind $actor, 
                         Tile $tile, bool $isDiscard) {
        $this->actor = $actor;
        $this->tile = $tile;
        $this->isDiscard = $isDiscard;
    }

    /**
     * @return SeatWind
     */
    function getActor() {
        return $this->actor;
    }

    /**
     * @return Tile
     */
    function getTile() {
        return $this->tile;
    }

    /**
     * @return boolean
     */
    function isDiscard() {
        return $this->isDiscard;
    }

    /**
     * @param Area $area
     * @return bool
     */
    function valid(Area $area) {
        $hand = $area->getHand();
        return $hand->getPrivate()->valueExist($this->getTile());
    }

    /**
     * @param Area $area
     */
    function apply(Area $area) {
        if (!$this->valid($area)) {
            throw new \InvalidArgumentException();
        }

        $hand = $area->getHand();
        $tile = $this->getTile();
        $isDiscard = $this->isDiscard();
        $targetType = TargetType::create($isDiscard
            ? TargetType::DISCARD
            : TargetType::KONG);

        $newPublic = $hand->getPrivate()->getCopy()
            ->remove($tile);
        $newMelded = $hand->getMelded();
        $newTarget = new Target($tile, $targetType, $this->getActor());

        $newHand = new Hand($newPublic, $newMelded, $newTarget);
        $area->setHand($newHand);

        $area->getAreas()->recordOpen($tile, $isDiscard);
    }
}