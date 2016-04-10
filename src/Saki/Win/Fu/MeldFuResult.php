<?php
namespace Saki\Win\Fu;

class MeldFuResult {
    private $meld;
    private $fu;

    function __construct($meld, $fu) {
        $this->meld = $meld;
        $this->fu = $fu;
    }

    function __toString() {
        return sprintf("%s: %s fu", $this->getMeld(), $this->getFu());
    }

    function getMeld() {
        return $this->meld;
    }

    function getFu() {
        return $this->fu;
    }
}