<?php
namespace Saki\Yaku;

use Saki\Util\ArrayLikeObject;

class YakuList extends ArrayLikeObject {
    // Yaku[] sorted in display order
    // totalYakuCount
    // YakuLevel: manguan, tiaoman, beiman, sanbeiman, yiman, shuangyiman, sanyiman

    private $isExposed;

    function __construct(array $innerArray, $isExposed) {
        parent::__construct($innerArray);
        if (!is_bool($isExposed)) {
            throw new \InvalidArgumentException();
        }
        $this->isExposed = $isExposed;
    }

    function isExposed() {
        return $this->isExposed;
    }

    function getFanCount() {
        $isExposed = $this->isExposed();
        $fanCounts = array_map(function (Yaku $yaku) use ($isExposed) {
            return $yaku->getFanCount($isExposed);
        }, $this->toArray());
        return array_sum($fanCounts);
    }
}