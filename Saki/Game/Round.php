<?php

namespace Saki\Game;

use Saki\FinalScore\FinalScoreStrategyTarget;
use Saki\Meld\QuadMeldType;
use Saki\RoundResult\ExhaustiveDrawRoundResult;
use Saki\RoundResult\OnTheWayDrawRoundResult;
use Saki\RoundResult\RoundResult;
use Saki\RoundResult\RoundResultType;
use Saki\RoundResult\WinRoundResult;
use Saki\Tile\Tile;
use Saki\Util\ArrayLikeObject;
use Saki\Win\WinAnalyzer;
use Saki\Win\WinTarget;

/*

## note: round logic

new phase

- reset and shuffle wall
- decide dealer player
- decide each player's wind

reset phase

- each player draw 4 tiles
- goto dealer player's private phase

p's private phase: before execute command

- when enter: turn++, draw 1 tile if allowed
- show candidate commands
- always: discard one of onHand tile
- sometime: kong, plusKong, zimo

p's private phase: after execute command

- if discard: go to public phase
- if zimo: go to round-over phase
- if kong/plusKong: drawBack, stay in private phase

p's public phase: basic version

- public phase means waiting for other's response for current player's action
- poller responsible for select a final action for public phase

p's public phase: before execute command

- only non-current players may have candidate commands
- if none candidate commands exist: goto next player's private phase if remainTileCount > 0, otherwise go to over phase
- if candidate commands exist, wait for each player's response, and execute the highest priority ones.
- command types: ron, chow, pon, kong

p's public phase: after execute command

- if ron: go to round-over phase
- if chow/pon/kong: go to execute player's private phase?

over phase

- draw or win
- calculate points and modify players' points
- new next round

*/
class Round {
    private $roundData;
    private $winAnalyzer;

    function __construct(RoundData $roundData = null) {
        $actualRoundData = $roundData ?: new RoundData();
        $this->roundData = $actualRoundData;
        $this->winAnalyzer = new WinAnalyzer($actualRoundData->getGameData()->getYakuSet());
        $this->toInitPhase();
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
        return $this->getRoundData()->getTurnManager()->getRoundPhase();
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
        return $this->getRoundData()->getTurnManager()->getCurrentPlayer();
    }

    function getWinAnalyzer() {
        return $this->winAnalyzer;
    }

    function getWinResult(Player $player) {
        // WinTarget will assert valid player
        return $this->getWinAnalyzer()->analyzeTarget(new WinTarget($player, $this->getRoundData()));
    }

    protected function toInitPhase() {
        // each player draw initial tiles
        $this->getRoundData()->getTileAreas()->drawInitForAll();
        // go to dealer player's private phase
        $this->getRoundData()->getTurnManager()->start();
        $this->toPrivatePhase($this->getRoundData()->getPlayerList()->getDealerPlayer(), true, true);
    }

    protected function toPrivatePhase(Player $player, $drawTile, $isFromInit = false) {
        if (!$isFromInit) {
            $this->getRoundData()->getTurnManager()->toPrivatePhase($player->getSelfWind());
        }

        if ($drawTile) {
            $this->getRoundData()->getTileAreas()->draw($player);
        }
    }

    protected function toPublicPhase() {
        $this->getRoundData()->getTurnManager()->toPublicPhase();
    }

    protected function toOverPhase(RoundResult $result) {
        // change phase and save result
        $this->getRoundData()->getTurnManager()->over($result);
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
                $keepDealer = $this->getRoundData()->getTurnManager()->getRoundResult()->isKeepDealer();
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
        return $this->getRoundData()->getGameData()->getFinalScoreStrategy()->getFinalScoreItems($target);
    }

    function toNextRound() {
        if ($this->getRoundPhase() != RoundPhase::getOverPhaseInstance()) {
            throw new \InvalidArgumentException('Not over phase.');
        }

        if ($this->isGameOver()) {
            throw new \InvalidArgumentException('Game is over.');
        }

        $keepDealer = $this->getRoundData()->getTurnManager()->getRoundResult()->isKeepDealer();
        $this->getRoundData()->reset($keepDealer);

        $this->toInitPhase();
    }

    function init() {
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
        $analyzer = $this->getWinAnalyzer()->getWaitingAnalyzer();
        $handList = $this->getRoundData()->getTileAreas()->getPrivateHand($player);
        $futureWaitingList = $analyzer->analyzePrivatePhaseFutureWaitingList($handList, $player->getTileArea()->getDeclaredMeldListReference());
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

        $currentTurn = $this->getRoundData()->getTurnManager()->getGlobalTurn();
        $isFirstTurn = $currentTurn == 1;
        $noDeclaredActions = !$this->getRoundData()->getTileAreas()->getDeclareHistory()->hasDeclare($currentTurn, Tile::fromString('E'));
        $validTileList = $this->getRoundData()->getTileAreas()->getPrivateHand($player)->isNineKindsOfTerminalOrHonor();

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
            $waitingAnalyzer = $this->getWinAnalyzer()->getWaitingAnalyzer();
            $roundData = $this->getRoundData();
            $isWaitingStates = array_map(function (Player $player) use ($waitingAnalyzer, $roundData) {
                $a13StyleHandTileList = $roundData->getTileAreas()->getPublicHand($player);
                $declaredMeldList = $player->getTileArea()->getDeclaredMeldListReference();
                $waitingTileList = $waitingAnalyzer->analyzePublicPhaseHandWaitingTileList($a13StyleHandTileList, $declaredMeldList);
                $isWaiting = $waitingTileList->count() > 0;
                return $isWaiting;
            }, $players);
            $result = new ExhaustiveDrawRoundResult($players, $isWaitingStates);
            $this->toOverPhase($result);
            return;
        }

        // fourWindDraw
        $isFirstRound = $this->getRoundData()->getTurnManager()->getGlobalTurn() == 1;
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
        $isFourReachDraw = $this->getPlayerList()->all(function (Player $player) {
            return $player->getTileArea()->isReach();
        });
        if ($isFourReachDraw) {
            $result = new OnTheWayDrawRoundResult($this->getPlayerList()->toArray(),
                RoundResultType::getInstance(RoundResultType::FOUR_REACH_DRAW));
            $this->toOverPhase($result);
            return;
        }

        $nextPlayer = $this->getRoundData()->getTurnManager()->getOffsetPlayer(1);
        $this->toPrivatePhase($nextPlayer, true);
    }

    protected function handleFourKongDraw() {
        // more than 4 declared-kong-meld by at least 2 targetList
        $declaredKongCounts = $this->getPlayerList()->toArray(function (Player $player) {
            return $player->getTileArea()->getDeclaredMeldListReference()->toFilteredTypesMeldList([QuadMeldType::getInstance()])->count();
        });
        $kongCount = array_sum($declaredKongCounts);
        $declaredKongCountArray = new ArrayLikeObject($declaredKongCounts);
        $kongPlayerCount = $declaredKongCountArray->getFilteredValueCount(function ($n) {
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