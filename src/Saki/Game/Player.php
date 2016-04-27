<?php
namespace Saki\Game;

/**
 * @package Saki\Game
 */
class Player {
    // immutable
    private $no;
    private $initialPoint;
    private $initialSeatWind;

    /**
     * @param int $no
     * @param int $initialPoint
     * @param SeatWind $initialSeatWind
     */
    function __construct(int $no, int $initialPoint, SeatWind $initialSeatWind) {
        $this->no = $no;
        $this->initialPoint = $initialPoint;
        $this->initialSeatWind = $initialSeatWind;
    }

    /**
     * @return string
     */
    function __toString() {
        return sprintf('p%s', $this->getNo());
    }

    /**
     * @return int
     */
    function getNo() {
        return $this->no;
    }

    /**
     * @return int
     */
    function getInitialPoint() {
        return $this->initialPoint;
    }

    /**
     * @return SeatWind
     */
    function getInitialSeatWind() {
        return $this->initialSeatWind;
    }
}

