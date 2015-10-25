<?php
namespace Saki\Win;

use Saki\Meld\Meld;
use Saki\Meld\MeldList;
use Saki\Meld\PairMeldType;
use Saki\Meld\WeakPairMeldType;
use Saki\Meld\WeakRunMeldType;
use Saki\Tile\Tile;
use Saki\Tile\TileSortedList;
use Saki\Util\ArrayLikeObject;
use Saki\Util\Enum;
use Saki\Util\Utils;

class TileSeries extends Enum {
    static function getComparator() {
        $descBestArray = [
            TileSeries::getInstance(self::FOUR_RUN_AND_ONE_PAIR),
            TileSeries::getInstance(self::FOUR_CONCEALED_TRIPLE_OR_QUAD_AND_ONE_PAIR),
            TileSeries::getInstance(self::FOUR_TRIPLE_OR_QUAD_AND_ONE_PAIR),
            TileSeries::getInstance(self::FOUR_WIN_SET_AND_ONE_PAIR),
            TileSeries::getInstance(self::SEVEN_PAIRS),
            TileSeries::getInstance(self::NOT_TILE_SERIES),
        ];
        return Utils::getComparatorByBestArray($descBestArray);
    }

    function compareTo(TileSeries $other) {
        $f = self::getComparator();
        return $f($this, $other);
    }

    const NOT_TILE_SERIES = 0;
    const FOUR_WIN_SET_AND_ONE_PAIR = 1;
    const FOUR_RUN_AND_ONE_PAIR = 2;
    const FOUR_TRIPLE_OR_QUAD_AND_ONE_PAIR = 3;
    const FOUR_CONCEALED_TRIPLE_OR_QUAD_AND_ONE_PAIR = 4;
    const SEVEN_PAIRS = 5;

    function exist() {
        return $this->getValue() != self::NOT_TILE_SERIES;
    }

    function existIn(MeldList $allMeldList) {
        $this->assertValidAllMeldList($allMeldList);

        switch ($this->getValue()) {
            case self::NOT_TILE_SERIES:
                throw new \LogicException();
            case self::FOUR_WIN_SET_AND_ONE_PAIR:
                return $allMeldList->isFourWinSetAndOnePair();
            case self::FOUR_RUN_AND_ONE_PAIR:
                return $allMeldList->isFourRunAndOnePair();
            case self::FOUR_TRIPLE_OR_QUAD_AND_ONE_PAIR:
                return $allMeldList->isFourTripleOrQuadAndOnePair();
            case self::FOUR_CONCEALED_TRIPLE_OR_QUAD_AND_ONE_PAIR:
                return $allMeldList->isFourTripleOrQuadAndOnePair(true);
            case self::SEVEN_PAIRS:
                return $allMeldList->isSevenUniquePairs(true);
            default:
                throw new \LogicException();
        }
    }

    function getWaitingType(MeldList $allMeldList, Tile $winTile, MeldList $declaredMeldList) {
        $this->assertValidAllMeldList($allMeldList);

        $handMeldList = new MeldList($allMeldList->toArray());
        $handMeldList->removeByValue($declaredMeldList->toArray());
        if (!$handMeldList->tileExist($winTile)) {
            return WaitingType::getInstance(WaitingType::NOT_WAITING);
        } elseif (!$this->existIn($allMeldList)) {
            return WaitingType::getInstance(WaitingType::NOT_WAITING);
        } else {
            $winTileMeldList = $handMeldList->toFilteredMeldList(function (Meld $meld) use ($winTile) {
                return $meld->canToWeakMeld($winTile);
            });
            $waitingTypes = $winTileMeldList->toArray(function (Meld $meld) use ($winTile) {
                return $meld->toWeakMeld($winTile)->getWaitingType();
            });
            $l = new ArrayLikeObject($waitingTypes);
            $waitingType = $l->getMax(WaitingType::getComparator());
            return $waitingType;
        }
    }

    function getWaitingTileList(MeldList $allMeldList, Tile $winTile, MeldList $declaredMeldList) {
        $this->assertValidAllMeldList($allMeldList);

        $handMeldList = new MeldList($allMeldList->toArray());
        $handMeldList->removeByValue($declaredMeldList->toArray());
        if (!$handMeldList->tileExist($winTile)) {
            return new TileSortedList([]);
        } elseif (!$this->existIn($allMeldList)) {
            return new TileSortedList([]);
        } else {
            $winTileMeldList = $handMeldList->toFilteredMeldList(function (Meld $meld) use ($winTile) {
                return $meld->canToWeakMeld($winTile);
            });

            $waitingTiles = [];
            foreach ($winTileMeldList as $winTileMeld) {
                $weakWinTileMeld = $winTileMeld->toWeakMeld($winTile);
                $publicHandMeldList = new MeldList($handMeldList->toArray());
                $publicHandMeldList->replaceByValue($winTileMeld, $weakWinTileMeld);

                $pairList = $publicHandMeldList->toFilteredTypesMeldList([PairMeldType::getInstance()]);
                $weakList = $publicHandMeldList->toFilteredTypesMeldList([WeakPairMeldType::getInstance(), WeakRunMeldType::getInstance()]);
                if (count($pairList) == 2) {
                    $weakMeldList = $pairList;
                } elseif (count($weakList) == 1) {
                    $weakMeldList = $weakList;
                } else {
                    throw new \LogicException(
                        sprintf('Invalid implementation. $meldList[%s]', $publicHandMeldList)
                    );
                }

                foreach ($weakMeldList as $weakMeld) {
                    $waitingTiles = array_merge($waitingTiles, $weakMeld->getWaitingTiles());
                }
            }

            $tileList = new TileSortedList($waitingTiles);
            $tileList->unique();
            return $tileList;
        }
    }

    protected function assertValidAllMeldList(MeldList $allMeldList) {
        $valid = $allMeldList->isCompleteCount();
        if (!$valid) {
            throw new \InvalidArgumentException(
                sprintf('Invalid $allMeldList[%s].', $allMeldList)
            );
        }
    }

    /**
     * @param $value
     * @return TileSeries
     */
    static function getInstance($value) {
        return parent::getInstance($value);
    }
}