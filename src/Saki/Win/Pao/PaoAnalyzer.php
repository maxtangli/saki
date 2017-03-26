<?php

namespace Saki\Win\Pao;

use Saki\Game\Meld\Meld;
use Saki\Game\Meld\MeldList;
use Saki\Game\SeatWind;
use Saki\Util\Singleton;

/**
 * @package Saki\Win\Pao
 */
class PaoAnalyzer extends Singleton {
    /**
     * @param SeatWind $from
     * @param SeatWind $to
     * @param MeldList $newMelded
     * @return bool|Pao
     */
    function analyzeClaimPaoOrFalse(SeatWind $from, SeatWind $to, MeldList $newMelded) {
        /** @var Meld $toMeld */
        $toMeld = $newMelded->getLast();

        if ($toMeld->isPungOrKong(false)) {
            $isBigThreeDragon = $newMelded->isThreeDragon(true);
            $isPungOrKongDragon = $toMeld->getFirst()->isDragon();
            if ($isBigThreeDragon && $isPungOrKongDragon) {
                $type = PaoType::create(PaoType::BIG_THREE_DRAGONS_PAO);
                return new Pao($from, $to, $type);
            }
        }

        if ($toMeld->isPungOrKong(false)) {
            $isBigFourWinds = $newMelded->isFourWinds(true);
            $isPungOrKongWind = $toMeld->getFirst()->isWind();
            if ($isBigFourWinds && $isPungOrKongWind) {
                $type = PaoType::create(PaoType::BIG_FOUR_WINDS_PAO);
                return new Pao($from, $to, $type);
            }
        }

        return false;
    }
}