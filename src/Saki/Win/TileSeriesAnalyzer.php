<?php

namespace Saki\Win;

use Saki\Meld\MeldList;
use Saki\Util\ArrayList;
use Saki\Util\Singleton;
use Saki\Win\TileSeries;

/**
 * Analyze TileSeries for a given complete hand MeldList.
 * @package Saki\Win
 */
class TileSeriesAnalyzer extends Singleton {

    /**
     * @return TileSeries[]
     */
    static function getDefaultTileSeriesArray() {
        return [
            TileSeries::getInstance(TileSeries::FOUR_WIN_SET_AND_ONE_PAIR),
            TileSeries::getInstance(TileSeries::SEVEN_PAIRS),
            TileSeries::getInstance(TileSeries::THIRTEEN_ORPHANS),
        ];
    }

    private $tileSeriesList;

    /**
     * @param TileSeries[] $tileSeriesArray
     */
    function __construct(array $tileSeriesArray = null) {
//        if ($tileSeriesArray) {
//            $valid = !empty($tileSeriesArray) && array_unique($tileSeriesArray) == $tileSeriesArray;
//            if (!$valid) {
//                throw new \InvalidArgumentException();
//            }
//            $actual = $tileSeriesArray;
//        } else {
//            $actual = self::getDefaultTileSeriesArray();
//        }

        $actual = $tileSeriesArray ?? self::getDefaultTileSeriesArray();
        $this->tileSeriesList = new ArrayList($actual, false);
    }

    /**
     * @return ArrayList
     */
    public function getTileSeriesList() {
        return $this->tileSeriesList;
    }

    /**
     * @param MeldList $allMeldList
     * @return TileSeries
     */
    function analyzeTileSeries(MeldList $allMeldList) {
        $existIn = function (TileSeries $series) use ($allMeldList) {
            return $series->existIn($allMeldList);
        };
        $default = TileSeries::getInstance(TileSeries::NOT_TILE_SERIES);
        return $this->getTileSeriesList()->getSingleOrDefault($existIn, $default);
    }
}