<?php
namespace Saki\Win\Point;

use Saki\Util\Singleton;

/**
 * @package Saki\Result
 */
class PointTable extends Singleton {
    private $fus = [20, 25, 30, 40, 50, 60, 70, 80, 90, 100, 110];
    private $fans = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13];

    /**
     * @return int[]
     */
    function getFus() {
        return $this->fus;
    }

    /**
     * @return int[]
     */
    function getFans() {
        return $this->fans;
    }

    /**
     * @param int $fan
     * @param int|null $fu
     * @return PointTableItem
     */
    function getPointItem(int $fan, int $fu = null) {
        $pointLevel = PointLevel::fromFanAndFu($fan, $fu);
        if ($pointLevel->isNone()) {
            $basePoint = $fu * intval(pow(2, $fan + 2));
        } elseif (!$pointLevel->isYakuMan()) {
            $m = [
                PointLevel::MANGAN => 2000,
                PointLevel::HANEMAN => 3000,
                PointLevel::BAIMAN => 4000,
                PointLevel::SANBAIMAN => 6000,
            ];
            $basePoint = $m[$pointLevel->getValue()];
        } else {
            $yakuManCount = intval($fan / 13);
            $basePoint = 8000 * $yakuManCount;
        }
        return new PointTableItem($basePoint);
    }
}