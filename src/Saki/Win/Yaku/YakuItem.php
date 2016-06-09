<?php
namespace Saki\Win\Yaku;

use Saki\Util\Immutable;

/**
 * @package Saki\Win\Yaku
 */
class YakuItem implements Immutable {
    private $yaku;
    private $fan;

    /**
     * @param Yaku $yaku
     * @param int $fan
     */
    function __construct(Yaku $yaku, int $fan) {
        $this->yaku = $yaku;
        $this->fan = $fan;
    }

    /**
     * @return string
     */
    function __toString() {
        return sprintf('%s,%s', $this->yaku, $this->fan);
    }

    /**
     * @return Yaku
     */
    function getYaku() {
        return $this->yaku;
    }

    /**
     * @return int
     */
    function getFan() {
        return $this->fan;
    }
}