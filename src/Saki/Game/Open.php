<?php
namespace Saki\Game;

use Saki\Game\Tile\Tile;
use Saki\Util\Immutable;

/**
 * @package Saki\Game
 */
class Open implements Immutable {
    private $area;
    private $openTile;
    private $isDiscard;

    /**
     * @param Area $area
     * @param Tile $openTile
     * @param bool $isDiscard
     */
    function __construct(Area $area,
                         Tile $openTile, bool $isDiscard) {
        $this->area = $area;
        $this->openTile = $openTile;
        $this->isDiscard = $isDiscard;
    }

    /**
     * @return SeatWind
     */
    function getActor() {
        return $this->getArea()->getSeatWind();
    }

    /**
     * @return Area
     */
    function getArea() {
        return $this->area;
    }

    /**
     * @return Tile
     */
    function getOpenTile() {
        return $this->openTile;
    }

    /**
     * @return boolean
     */
    function isDiscard() {
        return $this->isDiscard;
    }

    /**
     * @return bool
     */
    function valid() {
        // valid SwapCalling
        // note: seems not good to place here
        $area = $this->getArea();
        $round = $area->getRound();
        $swapCalling = $round->getRule()->getSwapCalling();
        $phaseState = $round->getPhaseState();
        $openTile = $this->getOpenTile();
        if (!$swapCalling->allowOpen($phaseState, $openTile)) {
//            throw new InvalidCommandException('', 'swap calling not allow open');
            return false;
        }

        // valid tile
        if ($area->getRiichiStatus()->isRiichi()) {
            $targetTile = $area->getHand()->getTarget()->getTile();
            return $openTile === $targetTile;
        } else {
            $private = $area->getHand()->getPrivate();
            return $private->valueExist($openTile, Tile::getPrioritySelector()); // handle red
        }
    }

    function apply() {
        if (!$this->valid()) {
            throw new \InvalidArgumentException();
        }
        $openTile = $this->getOpenTile();

        $area = $this->getArea();
        $hand = $area->getHand();
        $newPublic = $hand->getPrivate()
            ->remove($openTile, Tile::getPrioritySelector()); // handle red
        $newMelded = $hand->getMelded();
        $newTarget = Target::createNull();
        $newHand = new Hand($newPublic, $newMelded, $newTarget);

        $area->setHand($newHand);

        $round = $area->getRound();
        $round->getTurnHolder()->getOpenHistory()
            ->record(new OpenRecord($round->getTurnHolder()->getTurn(), $openTile, $this->isDiscard()));
    }
}