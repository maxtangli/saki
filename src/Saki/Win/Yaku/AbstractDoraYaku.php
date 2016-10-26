<?php
namespace Saki\Win\Yaku;

use Saki\Game\DoraFacade;
use Saki\Game\Tile\TileList;
use Saki\Win\WinSubTarget;

/**
 * @package Saki\Win\Yaku
 */
abstract class AbstractDoraYaku extends Yaku {
    function getConcealedFan() {
        return 1;
    }

    function getNotConcealedFan() {
        return 1;
    }

    protected function getExistCountImpl(WinSubTarget $subTarget) {
        $doraFacade = $subTarget->getWall()->getDoraFacade();
        $complete = $subTarget->getComplete();
        return $this->getDoraFanImpl($doraFacade, $complete);
    }

    function getRequiredSeries() {
        return [];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        return $this->getExistCountImpl($subTarget) > 0;
    }

    /**
     * @param DoraFacade $doraFacade
     * @param TileList $complete
     * @return int
     */
    abstract function getDoraFanImpl(DoraFacade $doraFacade, TileList $complete);
}