<?php
namespace Saki\Win\Yaku\Fan1;

use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

/**
 * note: may be simplified by introducing AbstractDoraYaku.
 * @package Saki\Win\Yaku\Fan1
 */
class RedDoraYaku extends Yaku {
    function getConcealedFan() {
        return 1;
    }

    function getNotConcealedFan() {
        return 1;
    }

    protected function getExistCountImpl(WinSubTarget $subTarget) {
        $doraFacade = $subTarget->getDoraFacade();
        $privateFull = $subTarget->getPrivateComplete();
        return $doraFacade->getHandRedDoraFan($privateFull);
    }

    function getRequiredSeries() {
        return [];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        $doraFacade = $subTarget->getDoraFacade();
        $privateFull = $subTarget->getPrivateComplete();
        return $doraFacade->getHandRedDoraFan($privateFull) > 0;
    }
}

