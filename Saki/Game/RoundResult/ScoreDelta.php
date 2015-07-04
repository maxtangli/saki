<?php
namespace Saki\Game\RoundResult;

class ScoreDelta {
    private $before;
    private $delta;

    function __construct($before, $delta) {
        $this->before = $before;
        $this->delta = $delta;
    }

    function getBefore() {
        return $this->before;
    }

    function getDelta() {
        return $this->delta;
    }

    function getAfter() {
        return $this->getBefore() + $this->getDelta();
    }
}