<?php
namespace Saki\Win;

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

    function normalize() {
        // if exist yaku-man yaku, remove all not-yaku-man yaku
        $yakuMans = array_filter($this->toArray(), function (Yaku $yaku) {
            return $yaku->isYakuMan();
        });
        if (count($yakuMans) > 0) {
            $this->setInnerArray($yakuMans);
        }

        // remove excluded yakus
        $excludedYakus = array_reduce($this->toArray(), function (array $carry, Yaku $yaku) {
            return array_merge($carry, $yaku->getExcludedYakus());
        }, []);
        $remainYakus = array_filter($this->toArray(), function (Yaku $yaku) use ($excludedYakus) {
            return !in_array($yaku, $excludedYakus);
        });
        $this->setInnerArray($remainYakus);
    }
}