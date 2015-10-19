<?php

namespace Saki\Game;

use Saki\FinalScore\FinalScoreStrategyTarget;
use Saki\Meld\QuadMeldType;
use Saki\RoundResult\ExhaustiveDrawRoundResult;
use Saki\RoundResult\OnTheWayDrawRoundResult;
use Saki\RoundResult\RoundResultType;
use Saki\RoundResult\RoundResult;
use Saki\RoundResult\WinRoundResult;
use Saki\Tile\Tile;
use Saki\Util\ArrayLikeObject;
use Saki\Win\WaitingAnalyzer;
use Saki\Win\WinAnalyzer;
use Saki\Win\WinState;
use Saki\Win\WinTarget;

class Round {
    private $roundData;
    private $roundResult;
    private $winAnalyzer;
    private $waitingAnalyzer;

    function __construct(RoundData $roundData = null) {
        $this->roundData = $roundData ?: new RoundData();
        $this->roundResult = null;
        $this->winAnalyzer = new WinAnalyzer();
        $this->waitingAnalyzer = new WaitingAnalyzer($this->winAnalyzer);
        $this->toInitPhase();
    }

    /**
     * @return RoundResult
     */
    function getRoundResult() {
        return $this->roundResult;
    }

    /**
     * @return RoundData
     */
    function getRoundData() {
        return $this->roundData;
    }

    /**
     * @return RoundPhase
     */
    function getRoundPhase() {
        return $this->getRoundData()->getRoundPhase();
    }

    protected function setRoundPhase(RoundPhase $roundPhase) {
        $this->getRoundData()->setRoundPhase($roundPhase);
    }

    function getWinAnalyzer() {
        return $this->winAnalyzer;
    }

    function getWinResult(Player $player) {
        // WinTarget will assert valid player
        return $this->getWinAnalyzer()->analyzeTarget(new WinTarget($player, $this->getRoundData()));
    }

    function getWaitingAnalyzer() {
        return $this->waitingAnalyzer;
    }

    /**
     * @return PlayerList
     */
    function getPlayerList() {
        return $this->getRoundData()->getPlayerList();
    }

    /**
     * @return Player
     */
    function getCurrentPlayer() {
        return $this->getRoundData()->getPlayerList()->getCurrentPlayer();
    }

    protected function toInitPhase() {
        $this->setRoundPhase(RoundPhase::getInitPhaseInstance());

        // each player draw initial tiles
        $this->getRoundData()->getTileAreas()->drawInitForAll();

        // go to dealer player's private phase
        $this->toPrivatePhase($this->getRoundData()->getPlayerList()->getDealerPlayer(), true);
    }

    /**
     * @param Player $player
     * @param bool $drawTile
     */
    protected function toPrivatePhase(Player $player, $drawTile) {
        $this->setRoundPhase(RoundPhase::getPrivatePhaseInstance());
        $this->getPlayerList()->toPlayer($player);
        if ($drawTile) {
            $this->getRoundData()->getTileAreas()->draw($player);
        }
    }

    protected function toPublicPhase() {
        $this->setRoundPhase(RoundPhase::getPublicPhaseInstance());
    }

    protected function toOverPhase(RoundResult $result) {
        $this->setRoundPhase(RoundPhase::getOverPhaseInstance());
        // save result
        $this->roundResult = $result;
        // modify scores
        foreach ($this->getPlayerList() as $player) {
            $afterScore = $result->getScoreDelta($player)->getAfter();
            $player->setScore($afterScore);
        }
        // clear accumulatedReachCount if isWin
        if ($result->getRoundResultType()->isWin()) {
            $this->getRoundData()->getTileAreas()->setAccumulatedReachCount(0);
        }
    }

    function isGameOver() {
        $isOverPhase = $this->getRoundPhase() == RoundPhase::getOverPhaseInstance();
        if (!$isOverPhase) {
            return false;
        }

        $roundData = $this->getRoundData();
        if ($roundData->getPlayerList()->hasMinusScorePlayer()) { // 有玩家被打飞，游戏结束
            return true;
        } elseif ($roundData->getRoundWindData()->isFinalRound()) { // 北入终局，游戏结束
            return true;
        } elseif (!$roundData->getRoundWindData()->isLastOrExtraRound()) { // 指定场数未达，游戏未结束
            return false;
        } else { // 达到指定场数
            $topPlayers = $this->getPlayerList()->getTopPlayers();
            if (count($topPlayers) != 1) {
                return false; // 并列第一，游戏未结束
            }

            $topPlayer = $topPlayers[0];
            $isTopPlayerEnoughScore = $topPlayer->getScore() >= 30000; // todo rule
            if (!$isTopPlayerEnoughScore) { // 若首位点数未达原点，游戏未结束
                return false;
            } else { // 首位点数达到原点，非连庄 或 连庄者达首位，游戏结束
                $keepDealer = $this->getRoundResult()->isKeepDealer();
                $dealerIsTopPlayer = $this->getRoundData()->getPlayerList()->getDealerPlayer() == $topPlayer;
                return (!$keepDealer || $dealerIsTopPlayer);
            }
        }
    }

    /**
     * @param bool $requireGameOver
     * @return \Saki\FinalScore\FinalScoreItem[]
     */
    function getFinalScoreItems($requireGameOver = true) {
        if ($requireGameOver && !$this->isGameOver()) {
            throw new \InvalidArgumentException('Game is not over.');
        }
        $target = new FinalScoreStrategyTarget($this->getPlayerList());
        return $this->getRoundData()->getFinalScoreStrategy()->getFinalScoreItems($target);
    }

    function toNextRound() {
        if ($this->getRoundPhase() != RoundPhase::getOverPhaseInstance()) {
            throw new \InvalidArgumentException('Not over phase.');
        }

        if ($this->isGameOver()) {
            throw new \InvalidArgumentException('Game is over.');
        }

        $keepDealer = $this->getRoundResult()->isKeepDealer();
        $this->getRoundData()->reset($keepDealer);
        $this->roundResult = null;

        $this->toInitPhase();
    }

    function discard(Player $player, Tile $selfTile) {
        $this->assertPrivatePhase($player);
        $this->getRoundData()->getTileAreas()->discard($player, $selfTile);
        // switch phase
        $this->toPublicPhase();
    }

    function reach(Player $player, Tile $selfTile) {
        $this->assertPrivatePhase($player);

        // assert waiting after discard
        $analyzer = $this->getWaitingAnalyzer();
        $handList = $player->getPlayerArea()->getHandTileSortedList();
        $futureWaitingList = $analyzer->analyzePrivatePhaseFutureWaitingList($handList, $player->getPlayerArea()->getDeclaredMeldList());
        $isWaiting = $futureWaitingList->count() > 0;
        if (!$isWaiting) {
            throw new \InvalidArgumentException('Reach condition violated: is waiting.');
        }

        $isValidTile = $futureWaitingList->isForWaitingDiscardedTile($selfTile);
        if (!$isValidTile) {
            throw new \InvalidArgumentException(
                sprintf('Reach condition violated: invalid discard tile [%s].', $selfTile)
            );
        }

        // do
        $this->getRoundData()->getTileAreas()->reach($player, $selfTile);

        // switch phase
        $this->toPublicPhase();

        // todo four reach draw
    }

    function kongBySelf(Player $player, Tile $selfTile) {
        $this->assertPrivatePhase($player);
        $this->getRoundData()->getTileAreas()->kongBySelf($player, $selfTile);
        if (!$this->handleFourKongDraw()) {
            // stay in private phase
        }
    }

    function plusKongBySelf(Player $player, Tile $selfTile) {
        $this->assertPrivatePhase($player);
        $this->getRoundData()->getTileAreas()->plusKongBySelf($player, $selfTile);
        if (!$this->handleFourKongDraw()) {
            // stay in private phase
        }
    }

    function winBySelf(Player $player) {
        $this->assertPrivatePhase($player);
        // do
        $roundResult = WinRoundResult::createWinBySelf($this->getPlayerList()->toArray(), $player, $this->getWinResult($player),
            $this->getRoundData()->getTileAreas()->getAccumulatedReachCount(), $this->getRoundData()->getRoundWindData()->getSelfWindTurn());
        // phase
        $this->toOverPhase($roundResult);
    }

    function nineKindsOfTerminalOrHonorDraw(Player $player) {
        // check
        $this->assertPrivatePhase($player);

        $currentTurn = $this->getRoundData()->getPlayerList()->getGlobalTurn();
        $isFirstTurn = $currentTurn == 1;
        $noDeclaredActions = !$this->getRoundData()->getTileAreas()->getDeclareHistory()->hasDeclare($currentTurn);
        $validTileList = $player->getPlayerArea()->getHandTileSortedList()->isNineKindsOfTerminalOrHonor();

        $valid = $isFirstTurn && $noDeclaredActions && $validTileList;
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        // do
        $roundResult = new OnTheWayDrawRoundResult($this->getPlayerList()->toArray(),
            RoundResultType::getInstance(RoundResultType::NINE_KINDS_OF_TERMINAL_OR_HONOR_DRAW));
        // phase
        $this->toOverPhase($roundResult);
    }

    protected function assertPrivatePhase($player) {
        $validPhase = $this->getRoundPhase() == RoundPhase::getPrivatePhaseInstance();
        if (!$validPhase) {
            throw new \InvalidArgumentException(
                sprintf('expected [private phase] but [%s] given.', $this->getRoundPhase())
            );
        }

        $validPlayer = $player == $this->getCurrentPlayer();
        if (!$validPlayer) {
            throw new \InvalidArgumentException(
                sprintf('expected current player [%s] but player [%s] given.', $this->getCurrentPlayer(), $player)
            );
        }
    }

    function passPublicPhase() {
        $this->assertPublicPhase();

        $isExhaustiveDraw = $this->getRoundData()->getTileAreas()->getWall()->getRemainTileCount() == 0;
        if ($isExhaustiveDraw) {
            $players = $this->getPlayerList()->toArray();
            $analyzer = $this->getWaitingAnalyzer();
            $roundData = $this->getRoundData();
            $isWaitingStates = array_map(function (Player $player) use ($analyzer, $roundData) {
                $handTileList = $player->getPlayerArea()->getHandTileSortedList();
                $declaredMeldList = $player->getPlayerArea()->getDeclaredMeldList();
                $waitingTileList = $analyzer->analyzePublicPhaseHandWaitingTileList($handTileList, $declaredMeldList);
                $isWaiting = $waitingTileList->count() > 0;
                return $isWaiting;
            }, $players);
            $result = new ExhaustiveDrawRoundResult($players, $isWaitingStates);
            $this->toOverPhase($result);
            return;
        }

        // fourWindDraw
        $isFirstRound = $this->getRoundData()->getPlayerList()->getGlobalTurn() == 1;
        if ($isFirstRound) {
            $allDiscardTileList = $this->getRoundData()->getTileAreas()->getDiscardHistory()->getAllDiscardTileList();
            if ($allDiscardTileList->count() == 4) {
                $allDiscardTileList->unique();
                $isFourSameWindDiscard = $allDiscardTileList->count() == 1 && $allDiscardTileList[0]->isWind();
                if ($isFourSameWindDiscard) {
                    $result = new OnTheWayDrawRoundResult($this->getPlayerList()->toArray(),
                        RoundResultType::getInstance(RoundResultType::FOUR_WIND_DRAW));
                    $this->toOverPhase($result);
                    return;
                }
            }
        }

        // fourReachDraw
        $isFourReachDraw = $this->getPlayerList()->all(function(Player $player) {
            return $player->getPlayerArea()->isReach();
        });
        if ($isFourReachDraw) {
            $result = new OnTheWayDrawRoundResult($this->getPlayerList()->toArray(),
                RoundResultType::getInstance(RoundResultType::FOUR_REACH_DRAW));
            $this->toOverPhase($result);
            return;
        }

        $this->toPrivatePhase($this->getPlayerList()->getNextPlayer(), true);
    }

    protected function handleFourKongDraw() {
        // more than 4 declared-kong-meld by at least 2 players
        $declaredKongCounts = $this->getPlayerList()->toArray(function(Player $player) {
            return $player->getPlayerArea()->getDeclaredMeldList()->toFilteredTypesMeldList([QuadMeldType::getInstance()])->count();
        });
        $kongCount = array_sum($declaredKongCounts);
        $declaredKongCountArray = new ArrayLikeObject($declaredKongCounts);
        $kongPlayerCount = $declaredKongCountArray->getMatchedValueCount(function($n) {
            return $n > 0;
        });

        $isFourKongDraw = $kongCount >= 4 && $kongPlayerCount >= 2;
        if ($isFourKongDraw) {
            $result = new OnTheWayDrawRoundResult($this->getPlayerList()->toArray(),
                RoundResultType::getInstance(RoundResultType::FOUR_KONG_DRAW));
            $this->toOverPhase($result);
            return true;
        } else {
            return false;
        }
    }

    function chowByOther(Player $player, Tile $tile1, Tile $tile2) {
        $this->assertPublicPhase($player);
        $this->getRoundData()->getTileAreas()->chowByOther($player, $tile1, $tile2, $this->getCurrentPlayer());
        // switch phase
        $this->toPrivatePhase($player, false);
    }

    function pongByOther(Player $player) {
        $this->assertPublicPhase($player);
        $this->getRoundData()->getTileAreas()->pongByOther($player, $this->getCurrentPlayer());
        // switch phase
        $this->toPrivatePhase($player, false);
    }

    function kongByOther(Player $player) {
        $this->assertPublicPhase($player);
        $this->getRoundData()->getTileAreas()->kongByOther($player, $this->getCurrentPlayer());

        if (!$this->handleFourKongDraw()) {
            // switch phase
            $this->toPrivatePhase($player, false);
        }
    }

    function plusKongByOther(Player $player) {
        $this->assertPublicPhase($player);
        $this->getRoundData()->getTileAreas()->plusKongByOther($player, $this->getCurrentPlayer());

        if (!$this->handleFourKongDraw()) {
            // switch phase
            $this->toPrivatePhase($player, false);
        }
    }

    function winByOther(Player $player) {
        $this->assertPublicPhase($player);
        // do
        $roundResult = WinRoundResult::createWinByOther($this->getPlayerList()->toArray(), $player, $this->getWinResult($player), $this->getCurrentPlayer(),
            $this->getRoundData()->getTileAreas()->getAccumulatedReachCount(), $this->getRoundData()->getRoundWindData()->getSelfWindTurn());
        // phase
        $this->toOverPhase($roundResult);
    }

    function multiWinByOther(array $players) {
        $playerArray = new ArrayLikeObject($players);
        $playerArray->walk(function (Player $player) {
            $this->assertPublicPhase($player);
        });
        // do
        $winResults = $playerArray->toArray(function (Player $player) {
            return $this->getWinResult($player);
        });
        $roundResult = WinRoundResult::createMultiWinByOther($this->getPlayerList()->toArray(), $players, $winResults, $this->getCurrentPlayer(),
            $this->getRoundData()->getTileAreas()->getAccumulatedReachCount(), $this->getRoundData()->getRoundWindData()->getSelfWindTurn());
        // phase
        $this->toOverPhase($roundResult);
    }

    protected function assertPublicPhase($player = null) {
        $valid = $this->getRoundPhase() == RoundPhase::getPublicPhaseInstance() && ($player != $this->getCurrentPlayer());
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
    }
}

//
//class CommandProcessor {
//
//    private $publicPhaseCommandPoller;
//
//    function toPublicPhase() {
//        $this->getPublicPhaseCommandPoller()->reset($this->getCandidateCommands());
//        $this->wonderIfPollerDecided();
//    }
//
//    protected function getPublicPhaseCommandPoller() {
//        if ($this->publicPhaseCommandPoller === null) {
//            $this->publicPhaseCommandPoller = new PublicPhaseCommandPoller([]);
//        }
//        return $this->publicPhaseCommandPoller;
//    }
//
//    protected function wonderIfPollerDecided() {
//        $poller = $this->getPublicPhaseCommandPoller();;
//        if ($poller->decided()) {
//            $todoCommands = $poller->getDecidedCommands();
//            if (!empty($todoCommands)) {
//                foreach ($todoCommands as $todoCommand) {
//                    $todoCommand->execute();
//                }
//            } else { // no decided commands
//                $this->toPrivatePhase($this->getNextPlayer(), true);
//            }
//        } else { // candidate commands exist
//            // waiting commands decided
//        }
//    }
//
//    function acceptCommand(Command $command) {
//        switch ($this->getRoundPhase()->getValue()) {
//            case RoundPhase::PRIVATE_PHASE:
//                $command->execute();
//                break;
//            case RoundPhase::PUBLIC_PHASE:
//                $this->getPublicPhaseCommandPoller()->acceptCommand($command);
//                $this->wonderIfPollerDecided();
//                break;
//            default:
//                throw new \LogicException();
//        }
//    }
//
//    /**
//     * @return Command[]
//     */
//    function getCandidateCommands() {
//        $candidateCommands = [];
//
//        switch ($this->getRoundPhase()->getValue()) {
//            case RoundPhase::PRIVATE_PHASE:
//                $currentPlayer = $this->getCurrentPlayer();
//                $currentPlayerArea = $this->getPlayerArea($currentPlayer);
//                foreach ($currentPlayerArea->getOnHandTileSortedList() as $onHandTile) {
//                    $candidateCommands[] = new DiscardCommand($this, $currentPlayer, $onHandTile);
//                }
//                if ($currentPlayerArea->hasCandidateTile()) {
//                    $candidateCommands[] = new DiscardCommand($this, $currentPlayer, $currentPlayerArea->getPrivateTargetTile());
//                }
//                $candidateCommands = array_unique($candidateCommands);
//                break;
//            case RoundPhase::PUBLIC_PHASE:
//                // nextPlayer chow
//
//                // non-currentPlayer pong/kang/ron
//                break;
//            case RoundPhase::OVER_PHASE:
//                break;
//            default:
//                throw new \LogicException();
//        }
//
//        return $candidateCommands;
//    }
//
//    function getCandidateCommand(Player $player) {
//        return array_values(array_filter($this->getCandidateCommands(), function (Command $v) use ($player) {
//            return $v->getPlayer() == $player;
//        }));
//    }
//}