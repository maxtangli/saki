<?php
namespace Saki\Command\Debug;

use Saki\Command\Command;
use Saki\Command\ParamDeclaration\IntParamDeclaration;
use Saki\Game\Round;

/**
 * @package Saki\Command\Debug
 */
class SkipCommand extends DebugCommand {
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
        while ($nRemainSkip-- > 0) {
            $nextSeatWind = $round->getCurrentSeatWind()->toNext();
            $skipToCommand = sprintf('skipTo %s true', $nextSeatWind);
            $round->process($skipToCommand);
        }
    }
    //endregion
}