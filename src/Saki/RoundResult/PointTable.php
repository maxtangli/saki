<?php
namespace Saki\RoundResult;

use Saki\Util\Singleton;

class PointTable extends Singleton {
    private $fus = [20, 25, 30, 40, 50, 60, 70, 80, 90, 100, 110];
    private $fans = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13];

    function getFus() {
        return $this->fus;
    }

    function getFans() {
        return $this->fans;
    }

    function getPointItem($fan, $fu = null) {
        $pointLevel = PointLevel::fromFanAndFu($fan, $fu);
        if ($pointLevel == PointLevel::create(PointLevel::NONE)) {
            $basePoint = $fu * intval(pow(2, $fan + 2));
        } elseif (!$pointLevel->isYakuMan()) {
            $m = [
                PointLevel::MAN_GAN => 2000,
                PointLevel::HANE_MAN => 3000,
                PointLevel::BAI_MAN => 4000,
                PointLevel::SAN_BAI_MAN => 6000,
            ];
            $basePoint = $m[$pointLevel->getValue()];
        } else {
            $yakuManCount = intval($fan / 13);
            $basePoint = 8000 * $yakuManCount;
        }
        return new PointTableItem($basePoint);
    }
}