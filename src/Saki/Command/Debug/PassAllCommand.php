<?php
namespace Saki\Command\Debug;

use Saki\Command\Command;
use Saki\Command\CommandContext;

/**
 * @package Saki\Command\Debug
 */
class PassAllCommand extends Command {
    //region Command impl
    static function getParamDeclarations() {
        return [];
    }
    //endregion

    /**
     * @param CommandContext $context
     */
    function __construct(CommandContext $context) {
        parent::__construct($context, []);
    }

    //region Command impl
    protected function executableImpl(CommandContext $context) {
        $phaseState = $context->getRound()->getAreas()->getPhaseState();
        return $phaseState->getPhase()->isPublic();
    }

    protected function executeImpl(CommandContext $context) {
        $context->getRound()->toNextPhase();
    }
    //endregion
}