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
    private $tileSeriesList;

    /**
     * @param TileSeries[] $tileSeriesArray
     */
    function __construct(array $tileSeriesArray) {
        $this->tileSeriesList = (new ArrayList($tileSeriesArray))->lock();
    }

    /**
     * @return ArrayList
     */
    function getTileSeriesList() {
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