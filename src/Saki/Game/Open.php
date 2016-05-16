<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Util\Immutable;

/**
 * @package Saki\Game
 */
class Open implements Immutable {
    private $actor;
    private $tile;
    private $isDiscard;

    /**
     * @param SeatWind $actor
     * @param Tile $tile
     * @param bool $isDiscard
     */
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

        $areas = $area->getAreas();
        $hand = $area->getHand();
        $tile = $this->getTile();
        $isDiscard = $this->isDiscard();

        $newPublic = $hand->getPrivate()->getCopy()
            ->remove($tile);
        $newMelded = $hand->getMelded();
//        $targetType = TargetType::create($isDiscard
//            ? TargetType::DISCARD
//            : TargetType::KONG);
//        $newTarget = new Target($tile, $targetType, $this->getActor());
        $newTarget = Target::createNull();
        $newHand = new Hand($newPublic, $newMelded, $newTarget);

        $area->setHand($newHand);

        $areas->getOpenHistory()
            ->record(new OpenRecord($areas->getTurn(), $tile, $isDiscard));
    }
}