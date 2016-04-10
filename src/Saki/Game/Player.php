<?php
namespace Saki\Game;

/**
 * A game player holding his own point, seatWind and tileArea.
 * @package Saki\Game
 */
class Player {
    // immutable
    private $no;
    private $initialPoint;
    private $initialSeatWind;
    // initialized by Areas
    private $area;

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
        return sprintf('player[%s] wind[%s] point[%s]', $this->getNo(), $this->getArea()->getSeatWind(), $this->getArea()->getPoint());
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

    /**
     * @return Area
     */
    function getArea() {
        if ($this->area === null) {
            throw new \BadMethodCallException('Bad method call on Area-uninitialized Player.');
        }
        return $this->area;
    }

    /**
     * @param Area $area
     */
    function setArea(Area $area) {
        if ($this->area !== null) {
            throw new \BadMethodCallException('Bad method call on Area-initialized Player,');
        }
        $this->area = $area;
    }
}

