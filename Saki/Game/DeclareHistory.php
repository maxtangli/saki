<?php
namespace Saki\Game;

class DeclareHistory {

    private $a;

    function __construct() {
        $this->a = [];
    }

    function reset() {
        $this->a = [];
    }

    function recordDeclare($turn) {
        $this->a[$turn] = true;
    }

    function hasDeclare($turn) {
        return isset($this->a[$turn]);
    }
}