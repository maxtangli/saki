<?php
namespace Saki\Win\Fu;

use Saki\Meld\Meld;

/**
 * @package Saki\Win\Fu
 */
class WinSetFuResult {
    private $meld;
    private $fu;

    /**
     * @param Meld $meld
     * @param int $fu
     */
    function __construct(Meld $meld, int $fu) {
        $this->meld = $meld;
        $this->fu = $fu;
    }

    /**
     * @return string
     */
    function __toString() {
        return sprintf("%s: %s fu", $this->getMeld(), $this->getFu());
    }

    /**
     * @return Meld
     */
    function getMeld() {
        return $this->meld;
    }

    /**
     * @return int
     */
    function getFu() {
        return $this->fu;
    }
}