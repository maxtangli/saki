<?php
namespace Saki\Game\Phase;

use Saki\Game\Phase;
use Saki\Game\Round;
use Saki\Win\Point\PointList;
use Saki\Win\Result\Result;
use Saki\Win\Result\WinResult;

/**
 * @package Saki\Game\Phase
 */
class OverPhaseState extends PhaseState {
    private $result;

    /**
     * @param Round $round
     * @param Result $result
     */
    function __construct(Round $round, Result $result) {
        parent::__construct($round);
        $this->result = $result;
    }

    /**
     * @return Result|WinResult
     */
    function getResult() {
        return $this->result;
    }

    function toNextRound() {
        if (!$this->canToNextRound()) {
            throw new \LogicException(
                'Failed to call toNextRound().'
            );
        }

        $round = $this->getRound();
        // roll round
        $result = $this->getResult();
        $keepDealer = $result->isKeepDealer();
        $isWin = $result->getResultType()->isWin();
        $round->roll($keepDealer, $isWin);
    }

    /**
     * @return PointList
     */
    function getFinalScore() {
        if (!$this->isGameOver()) {
            throw new \InvalidArgumentException('Game is not over.');
        }
        $round = $this->getRound();
        $scoreStrategy = $round->getRule()->getScoreStrategy();
        $raw = $round->getPointHolder()->getPointList();
        return $scoreStrategy->rawToFinal($raw);
    }

    //region PhaseState impl
    /**
     * @return bool
     */
    function isGameOver() {
        $round = $this->getRound();
        $pointList = $round->getPointHolder()->getPointList();
        $prevailingCurrent = $round->getPrevailing();

        if ($pointList->hasMinus()) {
            return true;
        }

        if ($prevailingCurrent->isNormalNotLast()) {
            return false;
        }

        if ($prevailingCurrent->isSuddenDeathLast()) {
            return true;
        } // else isNormalLast or isSuddenDeath

        if ($pointList->hasTiledTop()) {
            return false;
        }

        $topItem = $pointList->getSingleTop();
        $isTopEnoughPoint = $topItem->getPoint() >= 30000;
        if (!$isTopEnoughPoint) {
            return false;
        } // else isTopPlayerEnoughPoint

        $isDealerTop = $topItem->getSeatWind()->isDealer();
        $result = $round->getPhaseState()->getResult();
        return !($result->isKeepDealer()) || $isDealerTop;
    }

    function getPhase() {
        return Phase::createOver();
    }

    function getDefaultNextState() {
        return new InitPhaseState($this->getRound());
    }

    function enter() {
        // modify points
        $pointChangeMap = $this->getResult()->getPointChangeMap();
        $this->getRound()->getPointHolder()
            ->applyPointChangeMap($pointChangeMap);
    }

    function leave() {
        // do nothing
    }
    //endregion
}