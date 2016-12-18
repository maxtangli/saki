<?php
namespace Saki\Command\DebugCommand;

use Saki\Command\ParamDeclaration\BoolParamDeclaration;
use Saki\Command\ParamDeclaration\SeatWindParamDeclaration;
use Saki\Game\Round;
use Saki\Game\SeatWind;

/**
 * @package Saki\Command\Debug
 */
class SkipToCommand extends DebugCommand {
    //region Command impl
    static function getParamDeclarations() {
        return [SeatWindParamDeclaration::class, BoolParamDeclaration::class];
    }
    //endregion

    /**
     * @return SeatWind
     */
    function getSeatWind() {
        return $this->getParam(0);
    }

    /**
     * @return bool
     */
    function getIsPrivate() {
        return $this->getParam(1);
    }

    //region Command impl
    protected function executableImpl(Round $round) {
        return $round->getPhase()->isPrivateOrPublic();
    }

    protected function executeImpl(Round $round) {
        while (!$this->match($round)) {
            $phase = $round->getPhase();
            if ($phase->isPrivate()) {
                $actor = $round->getCurrentSeatWind();
                $area = $round->getArea($actor);
                $tile = $area->getHand()->getTarget()->getTile();
                $round->process("discard $actor $tile");
            } elseif ($phase->isPublic()) {
                $round->process('passAll');
            } else {
                throw new \LogicException();
            }
        }
    }

    //endregion

    /**
     * @param Round $round
     * @return bool
     */
    protected function match(Round $round) {
        if ($round->getPhase()->isPrivateOrPublic()) {
            return $round->getCurrentSeatWind() == $this->getSeatWind()
                && $round->getPhase()->isPrivate() == $this->getIsPrivate();
        } else {
            return true;
        }
    }
}