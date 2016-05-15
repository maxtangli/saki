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
    private $melded;

    function __construct(TargetHolder $targetHolder, SeatWind $seatWind) {
        $this->targetHolder = $targetHolder;
        $this->seatWind = $seatWind;

        $this->public = new TileList();
        $this->melded = new MeldList();
    }

    function init() {
        $this->public->removeAll();
        $this->melded->removeAll();
    }

    /**
     * @return Hand
     */
    function getHand() {
        return new Hand(
            $this->public,
            $this->melded,
            $this->targetHolder->getTarget($this->seatWind)
        );
    }

    /**
     * @param Hand $hand
     */
    function setHand(Hand $hand) {
        $this->public->fromSelect($hand->getPublic());
        $this->melded->fromSelect($hand->getMelded());
        $newTarget = $hand->getTarget();
        if ($newTarget->exist()) { // todo how to be more clear?
            $this->setTarget($newTarget);
        }
    }
}