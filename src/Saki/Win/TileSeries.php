<?php
namespace Saki\Win;

use Saki\Meld\Meld;
use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Util\Enum;

/**
 * A specific pattern for a complete private TileList.
 * Used in: no-yaku-false-win WinResult.
 * @package Saki\Win
 */
class TileSeries extends Enum {
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
        $this->assertValidAllMeldList($allMeldList);

        switch ($this->getValue()) {
            case self::NOT_TILE_SERIES:
                throw new \BadMethodCallException();
            case self::FOUR_WIN_SET_AND_ONE_PAIR:
                return $allMeldList->isFourWinSetAndOnePair();
            case self::SEVEN_PAIRS:
                return $allMeldList->isSevenUniquePairs();
            case self::THIRTEEN_ORPHANS:
                return $allMeldList->isThirteenOrphan(false);
            default:
                throw new \LogicException();
        }
    }

    /**
     * todo introduce Hand class which reduce param counts
     * @param MeldList $allMeldList
     * @param Tile $winTile
     * @param MeldList $declaredMeldList
     * @return WaitingType
     */
    function getWaitingType(MeldList $allMeldList, Tile $winTile, MeldList $declaredMeldList) {
        $this->assertValidAllMeldList($allMeldList);

        $handMeldList = $declaredMeldList->isEmpty() ? $allMeldList->getCopy()
            : $allMeldList->getCopy()->remove($declaredMeldList->toArray());

        if (!$handMeldList->tileExist($winTile) || !$this->existIn($allMeldList)) {
            return WaitingType::create(WaitingType::NOT_WAITING);
        }

        $winTileMeldList = $handMeldList->getCopy()->where(function (Meld $meld) use ($winTile) {
            return $meld->canToWeakMeld($winTile);
        });
        $waitingTypeList = $winTileMeldList->toArrayList(function (Meld $meld) use ($winTile) {
            return $meld->toWeakMeld($winTile)->getWaitingType();
        });
        $waitingType = $waitingTypeList->getMax(WaitingType::getComparator());
        return $waitingType;
    }

    protected function assertValidAllMeldList(MeldList $allMeldList) {
        $valid = $allMeldList->isCompletePrivateHandCount();
        if (!$valid) {
            throw new \InvalidArgumentException(
                sprintf('Invalid $allMeldList[%s].', $allMeldList)
            );
        }
    }
}