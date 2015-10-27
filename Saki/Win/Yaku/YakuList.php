<?php
namespace Saki\Win\Yaku;

use Saki\Util\ArrayLikeObject;

/**
 * Yaku[] sorted in display order.
 * @package Saki\Win\Yaku
 */
class YakuList extends ArrayLikeObject {
    private $isConcealed;

    function __construct(array $innerArray, $concealed) {
        if (!is_bool($concealed)) {
            throw new \InvalidArgumentException();
        }

        parent::__construct($innerArray);
        $this->isConcealed = $concealed;
    }

    function isConcealed() {
        return $this->isConcealed;
    }

    function getFanCount() {
        $concealed = $this->isConcealed();
        $fanCounts = array_map(function (Yaku $yaku) use ($concealed) {
            return $yaku->getFanCount($concealed);
        }, $this->toArray());
        return array_sum($fanCounts);
    }

    function normalize() {
        // if exist yaku-man yaku, remove all not-yaku-man yaku
        $yakuMans = $this->toFilteredArray(function (Yaku $yaku) {
            return $yaku->isYakuMan();
        });
        if (count($yakuMans) > 0) {
            $this->setInnerArray($yakuMans);
        }

        // remove excluded yakus
        /** @var ArrayLikeObject $excludeYakuArray */
        $excludeYakuArray = $this->toReducedValue(function (ArrayLikeObject $carry, Yaku $yaku) {
            $carry->push($yaku->getExcludedYakus());
            return $carry;
        }, new ArrayLikeObject([]));
        $remainYakus = $this->toFilteredArray(function (Yaku $yaku) use ($excludeYakuArray) {
            return !$excludeYakuArray->valueExist($yaku);
        });
        $this->setInnerArray($remainYakus);
    }
}