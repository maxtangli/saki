<?php

namespace Saki\Win\Series;

use Saki\Game\Meld\MeldList;
use Saki\Util\ArrayList;
use Saki\Util\Immutable;
use Saki\Util\Singleton;

/**
 * Analyze Series for a given complete hand MeldList.
 * @package Saki\Win
 */
class SeriesAnalyzer extends Singleton implements Immutable {
    private $seriesList;

    /**
     * @param Series[] $seriesArray
     */
    function __construct(array $seriesArray) {
        $this->seriesList = new ArrayList($seriesArray);
    }

    /**
     * @return ArrayList
     */
    function getSeriesList() {
        return $this->seriesList->getCopy();
    }

    /**
     * @param MeldList $allMeldList
     * @return Series
     */
    function analyzeSeries(MeldList $allMeldList) {
        $existIn = function (Series $series) use ($allMeldList) {
            return $series->existIn($allMeldList);
        };
        $default = Series::create(Series::NOT_TILE_SERIES);
        return $this->getSeriesList()->getSingleOrDefault($existIn, $default);
    }
}