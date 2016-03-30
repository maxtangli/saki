<?php
namespace Saki\Win;

use Saki\Meld\Meld;
use Saki\Meld\MeldList;
use Saki\Meld\PairMeldType;
use Saki\Meld\WeakPairMeldType;
use Saki\Meld\WeakRunMeldType;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\ArrayList;
use Saki\Util\Enum;
use Saki\Util\PriorityComparable;

/**
 * @package Saki\Win
 */
class TileSeries extends Enum {
    use PriorityComparable;

    function getPriority() {
        $m = [
            self::FOUR_RUN_AND_ONE_PAIR => 6,
            self::FOUR_CONCEALED_TRIPLE_OR_QUAD_AND_ONE_PAIR => 5,
            self::FOUR_TRIPLE_OR_QUAD_AND_ONE_PAIR => 4,
            self::FOUR_WIN_SET_AND_ONE_PAIR => 3,
            self::SEVEN_PAIRS => 2,
            self::NOT_TILE_SERIES => 1,
        ];
        return $m[$this->getValue()];
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
        if (!$declaredMeldList->isEmpty()) {
            $handMeldList->remove($declaredMeldList->toArray());
        }
        if (!$handMeldList->tileExist($winTile)) {
            return WaitingType::getInstance(WaitingType::NOT_WAITING);
        } elseif (!$this->existIn($allMeldList)) {
            return WaitingType::getInstance(WaitingType::NOT_WAITING);
        } else {
            $winTileMeldList = $handMeldList->toFilteredMeldList(function (Meld $meld) use ($winTile) {
                return $meld->canToWeakMeld($winTile);
            });
            $waitingTypeList = (new ArrayList())->fromSelected($winTileMeldList, function (Meld $meld) use ($winTile) {
                return $meld->toWeakMeld($winTile)->getWaitingType();
            });
            $waitingType = $waitingTypeList->getMax(WaitingType::getComparator());
            return $waitingType;
        }
    }

    function getWaitingTileList(MeldList $allMeldList, Tile $winTile, MeldList $declaredMeldList) {
        $this->assertValidAllMeldList($allMeldList);

        $handMeldList = new MeldList($allMeldList->toArray());
        if (!$declaredMeldList->isEmpty()) {
            $handMeldList->remove($declaredMeldList->toArray());
        }
        if (!$handMeldList->tileExist($winTile)) {
            return new TileList();
        } elseif (!$this->existIn($allMeldList)) {
            return new TileList();
        } else {
            $winTileMeldList = $handMeldList->toFilteredMeldList(function (Meld $meld) use ($winTile) {
                return $meld->canToWeakMeld($winTile);
            });

            $waitingTileList = new TileList();
            foreach ($winTileMeldList as $winTileMeld) {
                $weakWinTileMeld = $winTileMeld->toWeakMeld($winTile);
                $publicHandMeldList = new MeldList($handMeldList->toArray());
                $publicHandMeldList->replace($winTileMeld, $weakWinTileMeld);

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
                    /** @var Meld $weakMeld */
                    $weakMeld = $weakMeld;
                    $waitingTileList->concat($weakMeld->getWaitingTileList());
                }
            }

            return $waitingTileList->distinct()->orderByTileID();
        }
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