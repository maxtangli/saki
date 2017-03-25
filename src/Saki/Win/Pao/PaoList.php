<?php

namespace Saki\Win\Pao;

use Saki\Game\SeatWind;
use Saki\Util\ArrayList;

/**
 * @package Saki\Win\Pao
 */
class PaoList extends ArrayList {
    /**
     * @param SeatWind $from
     * @return bool
     */
    function existFrom(SeatWind $from) {
        $isFrom = function (Pao $pao) use ($from) {
            return $pao->getFrom() == $from;
        };
        return $this->any($isFrom);
    }

    /**
     * @param SeatWind $to
     * @return bool
     */
    function existTo(SeatWind $to) {
        $isTo = function (Pao $pao) use ($to) {
            return $pao->getTo() == $to;
        };
        return $this->any($isTo);
    }

    /**
     * @param SeatWind $from
     * @param SeatWind $to
     * @return bool
     */
    function existPair(SeatWind $from, SeatWind $to) {
        $isPair = function (Pao $pao) use ($from, $to) {
            return $pao->isPair($from, $to);
        };
        return $this->any($isPair);
    }
}