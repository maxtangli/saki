<?php
namespace Saki\RoundPhase;

use Saki\Game\Player;
use Saki\Game\RoundData;
use Saki\Game\RoundPhase;
use Saki\Meld\QuadMeldType;
use Saki\RoundResult\ExhaustiveDrawRoundResult;
use Saki\RoundResult\OnTheWayDrawRoundResult;
use Saki\RoundResult\RoundResultType;
use Saki\Util\ArrayLikeObject;

class PublicPhaseState extends RoundPhaseState {

    function getRoundPhase() {
        return RoundPhase::getPublicInstance();
    }

    function getDefaultNextState(RoundData $roundData) {
        $nextPlayer = $roundData->getTurnManager()->getOffsetPlayer(1);
        $shouldDrawTile = true;
        return new PrivatePhaseState($nextPlayer, $shouldDrawTile);
    }

    function enter(RoundData $roundData) {
        // do nothing
    }

    function leave(RoundData $roundData) {
        $this->handleDraw($roundData);
    }

    protected function handleDraw(RoundData $roundData) {
        $drawResult = $this->getDrawResult($roundData);
        if ($drawResult) {
            $this->setCustomNextState(new OverPhaseState($drawResult));
        }
    }

    protected function getDrawResult(RoundData $roundData) {
        // todo move to DrawRuler

        // ExhaustiveDraw todo test shouldDrawTile==false case
        $nextState = $this->getNextState($roundData);
        $isExhaustiveDraw = $nextState->getRoundPhase()->isPrivate()
            && $nextState->shouldDrawTile()
            && $roundData->getTileAreas()->getWall()->getRemainTileCount() == 0;
        if ($isExhaustiveDraw) {
            $players = $roundData->getPlayerList()->toArray();
            $waitingAnalyzer = $roundData->getWinAnalyzer()->getWaitingAnalyzer();
            $isWaitingStates = array_map(function (Player $player) use ($waitingAnalyzer, $roundData) {
                $a13StyleHandTileList = $roundData->getTileAreas()->getPublicHand($player);
                $declaredMeldList = $player->getTileArea()->getDeclaredMeldListReference();
                $waitingTileList = $waitingAnalyzer->analyzePublic($a13StyleHandTileList, $declaredMeldList);
                $isWaiting = $waitingTileList->count() > 0;
                return $isWaiting;
            }, $players);
            $result = new ExhaustiveDrawRoundResult($players, $isWaitingStates);
            return $result;
        }

        // FourWindDraw
        $isFirstRound = $roundData->getTurnManager()->getGlobalTurn() == 1;
        if ($isFirstRound) {
            $allDiscardTileList = $roundData->getTileAreas()->getDiscardHistory()->getAllDiscardTileList();
            if ($allDiscardTileList->count() == 4) {
                $allDiscardTileList->unique();
                $isFourSameWindDiscard = $allDiscardTileList->count() == 1 && $allDiscardTileList[0]->isWind();
                if ($isFourSameWindDiscard) {
                    $result = new OnTheWayDrawRoundResult($roundData->getPlayerList()->toArray(),
                        RoundResultType::getInstance(RoundResultType::FOUR_WIND_DRAW));
                    return $result;
                }
            }
        }

        // FourReachDraw
        $isFourReachDraw = $roundData->getPlayerList()->all(function (Player $player) {
            return $player->getTileArea()->isReach();
        });
        if ($isFourReachDraw) {
            $result = new OnTheWayDrawRoundResult($roundData->getPlayerList()->toArray(),
                RoundResultType::getInstance(RoundResultType::FOUR_REACH_DRAW));
            return $result;
        }

        // FourKongDraw: more than 4 declared-kong-meld by at least 2 targetList todo test
        $declaredKongCounts = $roundData->getPlayerList()->toArray(function (Player $player) {
            return $player->getTileArea()->getDeclaredMeldListReference()->toFilteredTypesMeldList([QuadMeldType::getInstance()])->count();
        });
        $kongCount = array_sum($declaredKongCounts);
        $kongPlayerCount = (new ArrayLikeObject($declaredKongCounts))->getFilteredValueCount(function ($n) {
            return $n > 0;
        });
        $isFourKongDraw = $kongCount >= 4 && $kongPlayerCount >= 2;
        if ($isFourKongDraw) {
            $result = new OnTheWayDrawRoundResult($roundData->getPlayerList()->toArray(),
                RoundResultType::getInstance(RoundResultType::FOUR_KONG_DRAW));
            return $result;
        }

        return false;
    }
}