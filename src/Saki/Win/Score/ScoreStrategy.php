<?php
namespace Saki\Win\Score;

use Saki\Game\PointItem;
use Saki\Game\PointSetting;
use Saki\Win\Point\PointList;

/**
 * @package Saki\Win\Score
 */
abstract class ScoreStrategy {
    private $pointSetting;

    /**
     * @param $pointSetting
     */
    function __construct(PointSetting $pointSetting) {
        $this->pointSetting = $pointSetting;
    }

    /**
     * @return PointSetting
     */
    function getPointSetting() {
        return $this->pointSetting;
    }

    /**
     * @param PointList $raw
     * @return PointList final PointList order by rank.
     */
    function rawToFinal(PointList $raw) {
        $rawToFinal = function (PointItem $item) {
            $pointDelta = $this->getPointDelta($item->getRank());
            $newPoint = $item->getPoint() + $pointDelta;
            return $item->toPointKeepRank($newPoint);
        };
        $finalItems = $raw->toArrayList($rawToFinal)->toArray();
        return (new PointList($finalItems))->toOrderByRank();
    }

    /**
     * @param int $rank
     * @return int
     */
    abstract function getPointDelta(int $rank);
}