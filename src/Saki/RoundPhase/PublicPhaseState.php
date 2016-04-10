<?php
namespace Saki\RoundPhase;

use Saki\Game\Player;
use Saki\Game\Round;
use Saki\Game\RoundPhase;
use Saki\Meld\QuadMeldType;
use Saki\RoundResult\ExhaustiveDrawRoundResult;
use Saki\RoundResult\OnTheWayDrawRoundResult;
use Saki\RoundResult\RoundResultType;
use Saki\Util\ArrayList;

class PublicPhaseState extends RoundPhaseState {

    private $robQuad;
    private $postLeave;

    function __construct() {
        $this->robQuad = false;
        $this->postLeave = function () {
        };
    }

    function isRobQuad() {
        return $this->robQuad;
    }

    function setRobQuad(bool $robQuad) {
        $this->robQuad = $robQuad;
    }

    function getPostLeave() {
        return $this->postLeave;
    }

    function setPostLeave(callable $postLeave) {
        $this->postLeave = $postLeave;
    }

    function getRoundPhase() {
        return RoundPhase::getPublicInstance();
    }

    function getDefaultNextState(Round $round) {
        $nextPlayer = $round->getTurnManager()->getOffsetPlayer(1);
        $shouldDrawTile = true;
        return new PrivatePhaseState($nextPlayer, $shouldDrawTile);
    }

    function enter(Round $round) {
        // do nothing
    }

    function leave(Round $round) {
        $this->handleDraw($round);
        call_user_func($this->getPostLeave());
    }

    protected function handleDraw(Round $round) {
        $drawResult = $this->getDrawResult($round);
        if ($drawResult) {
            $this->setCustomNextState(new OverPhaseState($drawResult));
        }
    }

    protected function getDrawResult(Round $round) {
        // todo move to DrawRuler

        // ExhaustiveDraw todo test shouldDrawTile==false case
        $nextState = $this->getNextState($round);
        $isExhaustiveDraw = $nextState->getRoundPhase()->isPrivate()
            && $nextState->shouldDrawTile()
            && $round->getAreas()->getWall()->getRemainTileCount() == 0;
        if ($isExhaustiveDraw) {
            $players = $round->getPlayerList()->toArray();
            $waitingAnalyzer = $round->getWinAnalyzer()->getWaitingAnalyzer();
            $isWaitingStates = array_map(function (Player $player) use ($waitingAnalyzer, $round) {
                $a13StyleHandTileList = $player->getTileArea()->getHand()->getPublic();
                $declaredMeldList = $player->getTileArea()->getHand()->getDeclare();
                $waitingTileList = $waitingAnalyzer->analyzePublic($a13StyleHandTileList, $declaredMeldList);
                $isWaiting = $waitingTileList->count() > 0;
                return $isWaiting;
            }, $players);
            $result = new ExhaustiveDrawRoundResult($players, $isWaitingStates);
            return $result;
        }

        // FourWindDraw
        $isFirstRound = $round->getTurnManager()->getGlobalTurn() == 1;
        if ($isFirstRound) {
            $allDiscardTileList = $round->getAreas()->getOpenHistory()->getAllDiscard();
            if ($allDiscardTileList->count() == 4) {
                $allDiscardTileList->distinct();
                $isFourSameWindDiscard = $allDiscardTileList->count() == 1 && $allDiscardTileList[0]->isWind();
                if ($isFourSameWindDiscard) {
                    $result = new OnTheWayDrawRoundResult($round->getPlayerList()->toArray(),
                        RoundResultType::create(RoundResultType::FOUR_WIND_DRAW));
                    return $result;
                }
            }
        }

        // FourReachDraw
        $isFourReachDraw = $round->getPlayerList()->all(function (Player $player) {
            return $player->getTileArea()->getReachStatus()->isReach();
        });
        if ($isFourReachDraw) {
            $result = new OnTheWayDrawRoundResult($round->getPlayerList()->toArray(),
                RoundResultType::create(RoundResultType::FOUR_REACH_DRAW));
            return $result;
        }

        // FourKongDraw: more than 4 declared-kong-meld by at least 2 targetList
        $declaredKongCountList = (new ArrayList())->fromSelect($round->getPlayerList(), function (Player $player) {
            return $player->getTileArea()->getHand()->getDeclare()->toFiltered([QuadMeldType::create()])->count();
        });
        $kongCount = $declaredKongCountList->getSum();
        $kongPlayerCount = (new ArrayList())->fromSelect($declaredKongCountList)->where(function ($n) {
            return $n > 0;
        })->count();

        $isFourKongDraw = $kongCount >= 4 && $kongPlayerCount >= 2;
        if ($isFourKongDraw) {
            $result = new OnTheWayDrawRoundResult($round->getPlayerList()->toArray(),
                RoundResultType::create(RoundResultType::FOUR_KONG_DRAW));
            return $result;
        }

        return false;
    }
}