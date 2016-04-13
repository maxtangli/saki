<?php
namespace Saki\Command\Debug;

use Saki\Command\Command;
use Saki\Command\CommandContext;

class PassAllCommand extends Command {
    static function getParamDeclarations() {
        return [];
    }

    function __construct(CommandContext $context) {
        parent::__construct($context, []);
    }

    protected function executableImpl(CommandContext $context) {
        return $this->getContext()->getRound()->getPhaseState()->getPhase()->isPublic();
    }

    protected function executeImpl(CommandContext $context) {
        $this->getContext()->getRound()->toNextPhase();
    }
}