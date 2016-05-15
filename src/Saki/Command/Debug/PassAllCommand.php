<?php
namespace Saki\Command\Debug;

use Saki\Command\Command;
use Saki\Command\CommandContext;
use Saki\Game\Target;

class PassAllCommand extends Command {
    static function getParamDeclarations() {
        return [];
    }

    function __construct(CommandContext $context) {
        parent::__construct($context, []);
    }

    protected function executableImpl(CommandContext $context) {
        return $context->getPhase()->isPublic();
    }

    protected function executeImpl(CommandContext $context) {
        $context->getRound()->toNextPhase();
    }
}