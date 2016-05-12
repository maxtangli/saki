<?php
namespace Saki\Game;

use Saki\Meld\MeldList;
use Saki\Tile\TileList;

/**
 * @package Saki\Game
 */
class HandHolder {
    private $targetHolder;
    private $seatWind;
    private $public;
    private $declare;

    function __construct(TargetHolder $targetHolder, SeatWind $seatWind) {
        $this->targetHolder = $targetHolder;
        $this->seatWind = $seatWind;

        $this->public = new TileList();
        $this->declare = new MeldList();
    }

    function init() {
        $this->public->removeAll();
        $this->declare->removeAll();
    }

    /**
     * @return Hand
     */
    function getHand() {
        return new Hand(
            $this->public,
            $this->declare,
            $this->targetHolder->getTarget($this->seatWind)
        );
    }

    /**
     * @param Hand $newHand
     */
    function setHand(Hand $newHand) {
        $this->public->fromSelect($newHand->getPublic());
        $this->declare->fromSelect($newHand->getDeclare());
        $newTarget = $newHand->getTarget();
        if ($newTarget->exist()) { // todo how to be more clear?
            $this->setTarget($newTarget);
        }
    }
}