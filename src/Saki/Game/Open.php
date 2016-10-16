<?php
namespace Saki\Game;

use Saki\Command\InvalidCommandException;
use Saki\Tile\Tile;
use Saki\Util\Immutable;

/**
 * @package Saki\Game
 */
class Open implements Immutable {
    private $actor;
    private $openTile;
    private $isDiscard;

    /**
     * @param SeatWind $actor
     * @param Tile $openTile
     * @param bool $isDiscard
     */
    function __construct(SeatWind $actor,
                         Tile $openTile, bool $isDiscard) {
        $this->actor = $actor;
        $this->openTile = $openTile;
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
     * @param Area $area
     * @return bool
     */
    function valid(Area $area) {
        // valid SwapCalling
        // note: seems not good to place here
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

    /**
     * @param Area $area
     */
    function apply(Area $area) {
        if (!$this->valid($area)) {
            throw new \InvalidArgumentException();
        }
        $openTile = $this->getOpenTile();

        $hand = $area->getHand();
        $newPublic = $hand->getPrivate()
            ->remove($openTile, Tile::getPrioritySelector()); // handle red
        $newMelded = $hand->getMelded();
        $newTarget = Target::createNull();
        $newHand = new Hand($newPublic, $newMelded, $newTarget);

        $area->setHand($newHand);

        $round = $area->getRound();
        $round->getOpenHistory()
            ->record(new OpenRecord($round->getTurn(), $openTile, $this->isDiscard()));
    }
}