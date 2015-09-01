<?php
namespace Saki\Win;

use Saki\Meld\Meld;
use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Util\Enum;
use Saki\Util\Utils;

class TileSeries extends Enum {
    static function getBestTileSeries(array $tileSeriesArray) {
        $bestTileSeriesArray = [
            TileSeries::getInstance(self::FOUR_RUN_AND_ONE_PAIR),
            TileSeries::getInstance(self::FOUR_CONCEALED_TRIPLE_OR_QUAD_AND_ONE_PAIR),
            TileSeries::getInstance(self::FOUR_TRIPLE_OR_QUAD_AND_ONE_PAIR),
            TileSeries::getInstance(self::FOUR_WIN_SET_AND_ONE_PAIR),
            TileSeries::getInstance(self::SEVEN_PAIRS),
            TileSeries::getInstance(self::NOT_TILE_SERIES),
        ];
        return Utils::getBestOne($bestTileSeriesArray, $tileSeriesArray);
    }

    const NOT_TILE_SERIES = 0;
    const FOUR_WIN_SET_AND_ONE_PAIR = 1;
    const FOUR_RUN_AND_ONE_PAIR = 2;
    const FOUR_TRIPLE_OR_QUAD_AND_ONE_PAIR = 3;
    const FOUR_CONCEALED_TRIPLE_OR_QUAD_AND_ONE_PAIR = 4;
    const SEVEN_PAIRS = 5;

    function existIn(MeldList $allMeldList) {
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

    function getWaitingType(MeldList $allMeldList, Tile $winTile) {
        if (!$allMeldList->tileExist($winTile)) {
            return WaitingType::getInstance(WaitingType::NOT_WAITING);
        } elseif (!$this->existIn($allMeldList)) {
            return WaitingType::getInstance(WaitingType::NOT_WAITING);
        } else {
            $winTileMeldList = $allMeldList->toFilteredMeldList(function (Meld $meld) use ($winTile) {
                return $meld->canToWeakMeld($winTile);
            });
            $waitingTypes = $winTileMeldList->toArray(function (Meld $meld) use ($winTile) {
                return $meld->toWeakMeld($winTile)->getWaitingType();
            });
            $waitingType = WaitingType::getBestWaitingType($waitingTypes);
            return $waitingType;
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