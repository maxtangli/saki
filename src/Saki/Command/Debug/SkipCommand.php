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
    
    //region Command impl
    protected function executableImpl(Round $round) {
        return $round->getPhase()->isPrivateOrPublic();
    }

    protected function executeImpl(Round $round) {
        $nRemainSkip = $this->getSkipCount();
        
        if ($nRemainSkip > 0 && $round->getPhase()->isPublic()) {
            $round->process('passAll');
            --$nRemainSkip;
        }
        
        while ($nRemainSkip-- > 0 && $round->getPhase()->isPrivate()) {
            $currentActor = $round->getCurrentSeatWind();
            $scripts = sprintf('mockHand %s C; discard %s C; passAll', $currentActor, $currentActor);
            $round->process($scripts);
        }
    }
    //endregion
}