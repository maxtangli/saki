<?php
namespace Saki\Command;

class MockCommandDecider implements CommandDecider {
    private $candidate;

    //region CommandDecider impl
    function clear() {
        $this->candidate = null;
    }

    function decided() {
        return isset($this->candidate);
    }

    function getDecided() {
        if (!$this->decided()) {
            throw new \InvalidArgumentException();
        }
        return $this->candidate;
    }

    function isDecidedCommand(Command $command) {
        return $this->decided() && $this->getDecided() == $command;
    }

    function allowSubmit(Command $command) {
        return !$this->decided();
    }

    function submit(Command $command) {
        if (!$this->allowSubmit($command)) {
            throw new \InvalidArgumentException();
        }
        $this->candidate = $command;
    }
    //endregion
}