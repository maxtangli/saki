<?php

namespace Saki\Win\TileSeries;

use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Util\Singleton;
use Saki\Win\WaitingType;

class TileSeriesAnalyzer extends Singleton {

    static function getDefaultTileSeriesArray() {
        return [
            FourConcealedTripleOrQuadAndOnePairTileSeries::getInstance(),
            FourTripleOrQuadAndOnePair::getInstance(),

            FourRunAndOnePairTileSeries::getInstance(),

            FourWinSetAnd1PairTileSeries::getInstance(),

            SevenPairsTileSeries::getInstance(),
        ];
    }

    private $tileSeriesArray;

    /**
     * @param TileSeries[] $tileSeriesArray
     */
    function __construct(array $tileSeriesArray = null) { // todo
        if ($tileSeriesArray !== null) {
            throw new \InvalidArgumentException('to be implemented.');
        }

        // note: sub class SHOULD be prior by its parent class to support analyzeTileSeries() implementation
        // todo sort by inherit-distance-to-TileSeries may be better
        $this->tileSeriesArray = $tileSeriesArray ? : self::getDefaultTileSeriesArray();
    }

    /**
     * @return TileSeries[] $tileSeriesArray
     */
    function getTileSeriesArray() {
        return $this->tileSeriesArray;
    }

    /**
     * @param MeldList $allMeldList
     * @return bool|TileSeries
     */
    function analyzeTileSeries(MeldList $allMeldList) {
        foreach ($this->getTileSeriesArray() as $tileSeries) {
            if ($tileSeries->existIn($allMeldList)) {
                return $tileSeries;
            }
        }
        return false;
    }

    /**
     * @param MeldList $allMeldList
     * @param Tile $winTile
     * @return WaitingType
     */
    function analyzeWaitingType(MeldList $allMeldList, Tile $winTile) {
        $tileSeries = $this->analyzeTileSeries($allMeldList);
        if ($tileSeries === false) {
            return WaitingType::getInstance(WaitingType::NOT_EXIST);
        } else {
            return $tileSeries->getWaitingType($allMeldList, $winTile);
        }
    }
}