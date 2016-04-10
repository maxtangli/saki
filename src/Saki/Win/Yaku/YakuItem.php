<?php
namespace Saki\Win\Yaku;

class YakuItem {
    private $yaku;
    private $fan;

    function __construct(Yaku $yaku, int $fan) {
        $this->yaku = $yaku;
        $this->fan = $fan;
    }

    function __toString() {
        return sprintf('%s,%s', $this->yaku, $this->fan);
    }

    function getYaku() {
        return $this->yaku;
    }

    function getFan() {
        return $this->fan;
    }
}