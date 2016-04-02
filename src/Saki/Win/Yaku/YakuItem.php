<?php
namespace Saki\Win\Yaku;

class YakuItem {
    private $yaku;
    private $fanCount;

    function __construct(Yaku $yaku, int $fanCount) {
        $this->yaku = $yaku;
        $this->fanCount = $fanCount;
    }

    function __toString() {
        return sprintf('%s,%s', $this->yaku, $this->fanCount);
    }

    function getYaku() {
        return $this->yaku;
    }

    function getFanCount() {
        return $this->fanCount;
    }
}