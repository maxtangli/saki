<?php
namespace Saki\Win\Point;

use Saki\Util\ArrayList;
use Saki\Util\Singleton;

/**
 * @package Saki\Win\Result
 */
class PointTable extends Singleton {
    private $fuList;
    private $fanList;

    protected function __construct() {
        $this->fanList = new ArrayList([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13]);
        $this->fuList = new ArrayList([20, 25, 30, 40, 50, 60, 70, 80, 90, 100, 110]);
    }

    /**
     * @return array
     */
    function getDealerSample() {
        $result = [];
        foreach ($this->fanList as $fan) {
            foreach ($this->fuList as $fu) {
                $fanAndFu = new FanAndFu($fan, $fu);
                $item = $this->getPointItem($fanAndFu);
                $winnerChange = $item->getWinnerPointChange(false, true);
                $loserChangeWhenTsumo = $item->getLoserPointChange(true, true, false);
                $result[$fan][$fu] = [$winnerChange, $loserChangeWhenTsumo];
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    function getLeisureSample() {
        $result = [];
        foreach ($this->fanList as $fan) {
            foreach ($this->fuList as $fu) {
                $fanAndFu = new FanAndFu($fan, $fu);
                $item = $this->getPointItem($fanAndFu);
                $winnerChange = $item->getWinnerPointChange(false, false);
                $leisureLoserChangeWhenTsumo = $item->getLoserPointChange(true, false, false);
                $dealerLoserChangeWhenTsumo = $item->getLoserPointChange(true, false, true);
                $result[$fan][$fu] = [$winnerChange, $leisureLoserChangeWhenTsumo, $dealerLoserChangeWhenTsumo];
            }
        }
        return $result;
    }

    /**
     * @param FanAndFu $fanAndFu
     * @return PointTableItem
     */
    function getPointItem(FanAndFu $fanAndFu) {
        list($fan, $fu) = $fanAndFu->toArray();
        $pointLevel = $fanAndFu->getPointLevel();
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