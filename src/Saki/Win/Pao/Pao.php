<?php

namespace Saki\Win\Pao;

use Saki\Game\SeatWind;

/**
 * @package Saki\Win\Pao
 */
class Pao {
    private $from;
    private $to;
    private $paoType;

    /**
     * @param SeatWind $fromSeatWind
     * @param SeatWind $toSeatWind
     * @param PaoType $paoType
     */
    function __construct(SeatWind $fromSeatWind, SeatWind $toSeatWind, PaoType $paoType) {
        if ($fromSeatWind == $toSeatWind) {
            throw new \InvalidArgumentException(
                "Invalid Pao: \$fromSeatWind[$fromSeatWind]==\$toSeatWind[$toSeatWind]"
            );
        }

        $this->from = $fromSeatWind;
        $this->to = $toSeatWind;
        $this->paoType = $paoType;
    }

    /**
     * @return string
     */
    function __toString() {
        return sprintf('%s,%s,%s', $this->getFrom(), $this->getTo(), $this->getPaoType());
    }

    /**
     * @return SeatWind
     */
    function getFrom() {
        return $this->from;
    }

    /**
     * @return SeatWind
     */
    function getTo() {
        return $this->to;
    }

    /**
     * @return PaoType
     */
    function getPaoType() {
        return $this->paoType;
    }

    /**
     * @param SeatWind $from
     * @param SeatWind $to
     * @return bool
     */
    function isPair(SeatWind $from, SeatWind $to) {
        return $this->getFrom() == $from
            && $this->getTo() == $to;
    }
}