<?php

namespace Saki\Win\Series;

use Saki\Game\Meld\Meld;
use Saki\Game\Meld\MeldList;
use Saki\Game\SubHand;
use Saki\Util\ArrayList;
use Saki\Util\Enum;
use Saki\Win\Waiting\WaitingType;

/**
 * A specific pattern for a complete private TileList.
 * Used in: no-yaku-false-win WinResult.
 * @package Saki\Win
 */
class Series extends Enum {
    const NOT_TILE_SERIES = 0;
    const FOUR_WIN_SET_AND_ONE_PAIR = 1;
    const SEVEN_PAIRS = 2;
    const THIRTEEN_ORPHANS = 3;

    /**
     * @return bool
     */
    function isExist() {
        return $this->getValue() != self::NOT_TILE_SERIES;
    }

    /**
     * @param MeldList $allMeldList
     * @return bool
     */
    function existIn(MeldList $allMeldList) {
        switch ($this->getValue()) {
            case self::NOT_TILE_SERIES:
                throw new \BadMethodCallException();
            case self::FOUR_WIN_SET_AND_ONE_PAIR:
                return $allMeldList->isFourWinSetAndOnePair(); // validate complete hand
            case self::SEVEN_PAIRS:
                return $allMeldList->isSevenUniquePairs(); // validate complete hand
            case self::THIRTEEN_ORPHANS:
                return $allMeldList->isThirteenOrphan(false); // validate complete hand
            default:
                throw new \LogicException();
        }
    }

    /**
     * @param SubHand $hand
     * @return ArrayList
     */
    private function getWaitingTypeList(SubHand $hand) {
        $winTile = $hand->getTarget()->getTile();
        $toWaitingType = function (Meld $meld) use ($winTile) {
            return $meld->getWeakMeldWaitingType($winTile);
        };
        return $hand->getPrivateMeldList()
            ->toArrayList($toWaitingType);
    }

    /**
     * @param SubHand $hand
     * @return WaitingType
     */
    function getMaxWaitingType(SubHand $hand) {
        return $this->getWaitingTypeList($hand)
            ->getMax(WaitingType::getPrioritySelector());
    }

    /**
     * @param SubHand $hand
     * @param WaitingType $waitingType
     * @return bool
     */
    function existWaitingType(SubHand $hand, WaitingType $waitingType) {
        return $this->getWaitingTypeList($hand)->valueExist($waitingType);
    }
}