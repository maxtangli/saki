<?php
namespace Saki\Game;

use Saki\Game\Meld\MeldList;
use Saki\Game\Tile\TileList;

/**
 * @package Saki\Game
 */
class HandHolder {
    // shared variable
    private $targetHolder;
    // game variable
    private $seatWind;
    // round variable
    private $public;
    private $melded;

    /**
     * @param TargetHolder $targetHolder
     * @param SeatWind $seatWind
     */
    function __construct(TargetHolder $targetHolder, SeatWind $seatWind) {
        $this->targetHolder = $targetHolder;

        $this->seatWind = $seatWind;

        $this->public = new TileList();
        $this->melded = new MeldList();
    }

    /**
     * @param SeatWind $seatWind
     */
    function init(SeatWind $seatWind) {
        $this->seatWind = $seatWind;

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
        if ($hand->getTarget()->exist()) {
            $this->targetHolder->setTarget($hand->getTarget());
        }
    }
}