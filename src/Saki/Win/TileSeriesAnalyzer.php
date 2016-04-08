<?php

namespace Saki\Win;

use Saki\Meld\MeldList;
use Saki\Util\ArrayList;
use Saki\Util\Singleton;

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
            TileSeries::create(TileSeries::FOUR_WIN_SET_AND_ONE_PAIR),
            TileSeries::create(TileSeries::SEVEN_PAIRS),
            TileSeries::create(TileSeries::THIRTEEN_ORPHANS),
        ];
    }

    private $tileSeriesList;

    /**
     * @param TileSeries[] $tileSeriesArray
     */
    function __construct(array $tileSeriesArray = null) {
        // todo validate
        $actual = $tileSeriesArray ?? self::getDefaultTileSeriesArray();
        $this->tileSeriesList = (new ArrayList($actual))->lock();
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
        $default = TileSeries::create(TileSeries::NOT_TILE_SERIES);
        return $this->getTileSeriesList()->getSingleOrDefault($existIn, $default);
    }
}