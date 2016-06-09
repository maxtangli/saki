<?php
namespace Saki\Win\Draw;

use Saki\Game\Round;
use Saki\Util\Singleton;
use Saki\Win\Result\Result;

/**
 * @package Saki\Win\Draw
 */
abstract class Draw extends Singleton {
    /**
     * @param Round $round
     * @return bool
     */
    function isDraw(Round $round) {
        $valid = $round->getPhaseState()->getPhase()->isPublic();
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        return $this->isDrawImpl($round);
    }

    /**
     * @param Round $round
     * @return Result
     */
    function getResult(Round $round) {
        if (!$this->isDraw($round)) {
            throw new \InvalidArgumentException();
        }
        return $this->getResultImpl($round);
    }

    //region subclass hooks
    /**
     * @param Round $round
     */
    abstract protected function isDrawImpl(Round $round);

    /**
     * @param Round $round
     * @return Result
     */
    abstract protected function getResultImpl(Round $round);
    //endregion
}