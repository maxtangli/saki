<?php
namespace Saki\Command\PublicCommand\Debug;

use Saki\Command\Command;

class DebugPassCommand extends Command {
    function executable() {
        return $this->getContext()->getRoundData()->getPhaseState()->getRoundPhase()->isPublic();
    }

    function executeImpl() {
        $this->getContext()->getRoundData()->toNextPhase();
    }
}