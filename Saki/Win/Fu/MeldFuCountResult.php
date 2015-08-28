<?php
namespace Saki\Win\Fu;

class MeldFuCountResult {
    private $meld;
    private $fuCount;

    function __construct($meld, $fuCount) {
        $this->meld = $meld;
        $this->fuCount = $fuCount;
    }

    function __toString() {
        return sprintf("%s: %s fu", $this->getMeld(), $this->getFuCount());
    }

    function getMeld() {
        return $this->meld;
    }

    function getFuCount() {
        return $this->fuCount;
    }
}