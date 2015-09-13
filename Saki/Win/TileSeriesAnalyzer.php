<?php

namespace Saki\Win;

use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Util\ArrayLikeObject;
use Saki\Util\Singleton;

class TileSeriesAnalyzer extends Singleton {

    static function getDefaultTileSeriesArray() {
        return [
            TileSeries::getInstance(TileSeries::FOUR_RUN_AND_ONE_PAIR),
            TileSeries::getInstance(TileSeries::FOUR_CONCEALED_TRIPLE_OR_QUAD_AND_ONE_PAIR),
            TileSeries::getInstance(TileSeries::FOUR_TRIPLE_OR_QUAD_AND_ONE_PAIR),
            TileSeries::getInstance(TileSeries::FOUR_WIN_SET_AND_ONE_PAIR),
            TileSeries::getInstance(TileSeries::SEVEN_PAIRS),
        ];
    }

    private $tileSeriesArray;

    /**
     * @param TileSeries[] $tileSeriesArray
     */
    function __construct(array $tileSeriesArray = null) {
        if ($tileSeriesArray) {
            $valid = !empty($tileSeriesArray) && array_unique($tileSeriesArray) == $tileSeriesArray;
            if (!$valid) {
                throw new \InvalidArgumentException();
            }
            $this->tileSeriesArray = $tileSeriesArray;
        } else {
            $this->tileSeriesArray = self::getDefaultTileSeriesArray();
        }
    }

    /**
     * @return TileSeries[] $tileSeriesArray
     */
    function getTileSeriesArray() {
        return $this->tileSeriesArray;
    }

    /**
     * @param MeldList $allMeldList
     * @return TileSeries
     */
    function analyzeTileSeries(MeldList $allMeldList) {
        $candidates = [];
        foreach ($this->getTileSeriesArray() as $tileSeries) {
            if ($tileSeries->existIn($allMeldList)) {
                $candidates[] = $tileSeries;
            }
        }
        if (!empty($candidates)) {
            $l = new ArrayLikeObject($candidates);
            return $l->getMax(TileSeries::getComparator());
        } else {
            return TileSeries::getInstance(TileSeries::NOT_TILE_SERIES);
        }
    }
}