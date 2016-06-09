<?php
namespace Saki\Command\Debug;

use Saki\Command\Command;
use Saki\Command\CommandContext;
use Saki\Command\ParamDeclaration\IntParamDeclaration;

/**
 * @package Saki\Command\Debug
 */
class SkipCommand extends Command {
    //region Command impl
    static function getParamDeclarations() {
        return [IntParamDeclaration::class];
    }
    //endregion

    /**
     * @param CommandContext $context
     * @param int $skipCount
     */
    function __construct(CommandContext $context, int $skipCount) {
        parent::__construct($context, [$skipCount]);
    }

    /**
     * @return int
     */
    function getSkipCount() {
        return $this->getParam(0);
    }

    /**
     * @return bool
     */
    protected function isPrivate() {
        $phaseState = $this->getContext()->getRound()->getAreas()->getPhaseState();
        return $phaseState->getPhase()->isPrivate();
    }

    //region Command impl
    protected function executableImpl(CommandContext $context) {
        return $this->isPrivate();
    }

    protected function executeImpl(CommandContext $context) {
        $processor = $this->getContext()->getRound()->getProcessor();
        $nTodo = $this->getSkipCount();
        while ($nTodo-- > 0 && $this->isPrivate()) {
            $currentSeatWind = $context->getAreas()->getCurrentSeatWind();
            $scripts = sprintf('mockHand %s C; discard %s C; passAll', $currentSeatWind, $currentSeatWind);
            $processor->process($scripts);
        }
    }
    //endregion
}