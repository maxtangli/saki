<?php
namespace Saki\Yaku;

use Saki\Util\ArrayLikeObject;

class YakuList extends ArrayLikeObject {
    // Yaku[] sorted in display order
    // totalYakuCount
    // YakuLevel: manguan, tiaoman, beiman, sanbeiman, yiman, shuangyiman, sanyiman

    function getFanCount($isExposed) {
        $fanCounts = array_map(function (Yaku $yaku) use ($isExposed) {
            return $yaku->getFanCount($isExposed);
        }, $this->toArray());
        return array_sum($fanCounts);
    }
}