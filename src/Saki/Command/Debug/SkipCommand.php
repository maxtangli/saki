<?php
namespace Saki\Command\Debug;

use Saki\Command\Command;
use Saki\Command\ParamDeclaration\IntParamDeclaration;
use Saki\Game\Round;

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
     * @return int
     */
    function getSkipCount() {
        return $this->getParam(0);
    }

    /**
     * @return bool
     */
    protected function isPrivate() {
        $phaseState = $this->getRound()->getPhaseState();
        return $phaseState->getPhase()->isPrivate();
    }

    //region Command impl
    protected function executableImpl(Round $round) {
        return $this->isPrivate();
    }

    protected function executeImpl(Round $round) {

        $nRemainSkip = $this->getSkipCount();
        while ($nRemainSkip-- > 0 && $this->isPrivate()) {
            $currentSeatWind = $round->getCurrentSeatWind();
            $scripts = sprintf('mockHand %s C; discard %s C; passAll', $currentSeatWind, $currentSeatWind);
            $round->process($scripts);
        }
    }
    //endregion
}