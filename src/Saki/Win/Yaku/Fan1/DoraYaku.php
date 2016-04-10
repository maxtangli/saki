<?php
namespace Saki\Win\Yaku\Fan1;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

class DoraYaku extends Yaku {
    function getConcealedFanCount() {
        return 1;
    }

    function getNotConcealedFanCount() {
        return 1;
    }

    protected function getExistCountImpl(WinSubTarget $subTarget) {
        $doraFacade = $subTarget->getDoraFacade();
        $privateFull = $subTarget->getPrivateComplete();
        return $doraFacade->getHandDoraFanCount($privateFull);
    }

    function getRequiredTileSeries() {
        return [];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        $doraFacade = $subTarget->getDoraFacade();
        $privateFull = $subTarget->getPrivateComplete();
        return $doraFacade->getHandDoraFanCount($privateFull) > 0;
    }
}

