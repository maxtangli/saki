<?php
namespace Saki\Command;

use Saki\Command\PublicCommand\PublicCommand;

class MockCommandDecider implements CommandDecider {
    private $candidate;

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

    function isDecidedCommand(PublicCommand $publicCommand) {
        return $this->decided() && $this->getDecided() == $publicCommand;
    }

    function allowSubmit(PublicCommand $publicCommand) {
        return !$this->decided();
    }

    function submit(PublicCommand $publicCommand) {
        if (!$this->allowSubmit($publicCommand)) {
            throw new \InvalidArgumentException();
        }
        $this->candidate = $publicCommand;
    }
}