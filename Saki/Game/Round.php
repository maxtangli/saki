<?php

namespace Saki\Game;

use Saki\Command\CommandContext;
use Saki\Command\DiscardCommand;
use Saki\Meld\QuadMeldType;
use Saki\RoundPhase\OverPhaseState;
use Saki\RoundPhase\PrivatePhaseState;
use Saki\RoundResult\ExhaustiveDrawRoundResult;
use Saki\RoundResult\OnTheWayDrawRoundResult;
use Saki\RoundResult\RoundResult;
use Saki\RoundResult\RoundResultType;
use Saki\RoundResult\WinRoundResult;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\ArrayLikeObject;
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

    function __construct(RoundData $roundData = null) {
        $actualRoundData = $roundData ?? new RoundData();
        $this->roundData = $actualRoundData; // nullPhase now
//        $this->getRoundData()->toInitPhase();
        $this->getRoundData()->toNextPhase(); // initPhase now
        $this->getRoundData()->toNextPhase(); // privatePhase now
    }

    // todo move into Command
    function debugSkipTo(Player $currentPlayer, RoundPhase $roundPhase = null, $globalTurn = null,
                         Tile $mockDiscardTile = null) {
        $validCurrentState = $this->getRoundPhase()->isPrivateOrPublic();
        if (!$validCurrentState) {
            throw new \InvalidArgumentException();
        }

        $actualRoundPhase = $roundPhase ?? RoundPhase::getPrivateInstance();
        $validRoundPhase = $actualRoundPhase->isPrivateOrPublic();
        if (!$validRoundPhase) {
            throw new \InvalidArgumentException();
        }

        $actualGlobalTurn = $globalTurn ?? 1;
        $validActualGlobalTurn = ($actualGlobalTurn == 1);
        if (!$validActualGlobalTurn) {
            throw new \InvalidArgumentException('Not implemented.');
        }

        $actualMockDiscardTile = $mockDiscardTile ?? Tile::fromString('C');
        $validMockDiscardTile = !$actualMockDiscardTile->isWind();
        if (!$validMockDiscardTile) {
            throw new \InvalidArgumentException('Not implemented: consider FourWindDiscardedDraw issue.');
        }

        $isTargetTurn = function () use ($currentPlayer, $actualRoundPhase) {
            $isTargetTurn = ($this->getCurrentPlayer() == $currentPlayer) && ($this->getRoundPhase() == $actualRoundPhase);
            return $this->getRoundData()->isGameOver() || $isTargetTurn;
        };
        while (!$isTargetTurn()) {
            if ($this->getRoundPhase()->isPrivate()) {
                $this->debugDiscardByReplace($this->getCurrentPlayer(), $actualMockDiscardTile);
            } elseif ($this->getRoundPhase()->isPublic()) {
                $this->passPublicPhase();
            } else {
                throw new \LogicException();
            }
        }

        if ($this->getRoundData()->getTurnManager()->getGlobalTurn() != 1) {
            throw new \LogicException('Not implemented.');
        }
    }

    // command
    function debugDiscardByReplace(Player $player, Tile $discardTile, TileList $replaceHandTileList = null) {
        $actualReplaceHandTileList = $replaceHandTileList ?? new TileList([$discardTile]);
        $this->getRoundData()->getTileAreas()->debugReplaceHand($player, $actualReplaceHandTileList);

        $this->discard($player, $discardTile);
    }

    // command
    function debugReachByReplace(Player $player, Tile $reachTile, TileList $replaceHandTileList = null) {
        $actualReplaceHandTileList = $replaceHandTileList ?? new TileList([$reachTile]);
        $this->getRoundData()->getTileAreas()->debugReplaceHand($player, $actualReplaceHandTileList);

        $this->reach($player, $reachTile);
    }

    // command
    function debugKongBySelfByReplace(Player $player, Tile $selfTile) {
        $this->getRoundData()->getTileAreas()->debugReplaceHand($player, new TileList([$selfTile, $selfTile, $selfTile, $selfTile]));
        $this->kongBySelf($player, $selfTile);
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
//        return $this->getRoundData()->getTurnManager()->getRoundPhase();
        return $this->getRoundData()->getPhaseState()->getRoundPhase();
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

    // todo remove
    function getWinResult(Player $player) {
        // WinTarget will assert valid player
        return $this->getRoundData()->getWinAnalyzer()->analyzeTarget(new WinTarget($player, $this->getRoundData()));
    }

    // todo remove
    function discard(Player $player, Tile $selfTile) {
        (new DiscardCommand(new CommandContext($this), $player->getSelfWind(), $selfTile))->execute();
    }

    function reach(Player $player, Tile $selfTile) {
        $this->assertPrivatePhase($player);

        // assert waiting after discard
        $analyzer = $this->getRoundData()->getWinAnalyzer()->getWaitingAnalyzer();
        $handList = $this->getRoundData()->getTileAreas()->getPrivateHand($player);
        $futureWaitingList = $analyzer->analyzePrivate($handList, $player->getTileArea()->getDeclaredMeldListReference());
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
//        $this->getRoundData()->toPublicPhase();
        $this->getRoundData()->toNextPhase();

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
        $result = WinRoundResult::createWinBySelf($this->getPlayerList()->toArray(), $player, $this->getWinResult($player),
            $this->getRoundData()->getTileAreas()->getAccumulatedReachCount(), $this->getRoundData()->getRoundWindData()->getSelfWindTurn());
        // phase
//        $this->getRoundData()->toOverPhase($roundResult);
        $this->getRoundData()->toNextPhase(new OverPhaseState($result));
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
        $result = new OnTheWayDrawRoundResult($this->getPlayerList()->toArray(),
            RoundResultType::getInstance(RoundResultType::NINE_KINDS_OF_TERMINAL_OR_HONOR_DRAW));
        // phase
        $this->getRoundData()->toNextPhase(new OverPhaseState($result));
    }

    protected function assertPrivatePhase($player) {
        $validPhase = $this->getRoundPhase() == RoundPhase::getPrivateInstance();
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
            $waitingAnalyzer = $this->getRoundData()->getWinAnalyzer()->getWaitingAnalyzer();
            $roundData = $this->getRoundData();
            $isWaitingStates = array_map(function (Player $player) use ($waitingAnalyzer, $roundData) {
                $a13StyleHandTileList = $roundData->getTileAreas()->getPublicHand($player);
                $declaredMeldList = $player->getTileArea()->getDeclaredMeldListReference();
                $waitingTileList = $waitingAnalyzer->analyzePublic($a13StyleHandTileList, $declaredMeldList);
                $isWaiting = $waitingTileList->count() > 0;
                return $isWaiting;
            }, $players);
            $result = new ExhaustiveDrawRoundResult($players, $isWaitingStates);
            $this->getRoundData()->toNextPhase(new OverPhaseState($result));
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
                    $this->getRoundData()->toNextPhase(new OverPhaseState($result));
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
            $this->getRoundData()->toNextPhase(new OverPhaseState($result));
            return;
        }

        $nextPlayer = $this->getRoundData()->getTurnManager()->getOffsetPlayer(1);
//        $this->getRoundData()->toPrivatePhase($nextPlayer, true);
        $this->getRoundData()->toNextPhase();
    }

    protected function handleFourKongDraw() {
        // more than 4 declared-kong-meld by at least 2 targetList
        $declaredKongCounts = $this->getPlayerList()->toArray(function (Player $player) {
            return $player->getTileArea()->getDeclaredMeldListReference()->toFilteredTypesMeldList([QuadMeldType::getInstance()])->count();
        });
        $kongCount = array_sum($declaredKongCounts);
        $kongPlayerCount = (new ArrayLikeObject($declaredKongCounts))->getFilteredValueCount(function ($n) {
            return $n > 0;
        });

        $isFourKongDraw = $kongCount >= 4 && $kongPlayerCount >= 2;
        if ($isFourKongDraw) {
            $result = new OnTheWayDrawRoundResult($this->getPlayerList()->toArray(),
                RoundResultType::getInstance(RoundResultType::FOUR_KONG_DRAW));
            $this->getRoundData()->toNextPhase(new OverPhaseState($result));
            return true;
        } else {
            return false;
        }
    }

    function chowByOther(Player $player, Tile $tile1, Tile $tile2) {
        $this->assertPublicPhase($player);
        $this->getRoundData()->getTileAreas()->chowByOther($player, $tile1, $tile2, $this->getCurrentPlayer());
        // switch phase
//        $this->getRoundData()->toPrivatePhase($player, false);
        $this->getRoundData()->getPhaseState()->setCustomNextState(
            new PrivatePhaseState($player, false)
        );
        $this->getRoundData()->toNextPhase();
    }

    function pongByOther(Player $player) {
        $this->assertPublicPhase($player);
        $this->getRoundData()->getTileAreas()->pongByOther($player, $this->getCurrentPlayer());
        // switch phase
//        $this->getRoundData()->toPrivatePhase($player, false);
        $this->getRoundData()->getPhaseState()->setCustomNextState(
            new PrivatePhaseState($player, false)
        );
        $this->getRoundData()->toNextPhase();
    }

    function kongByOther(Player $player) {
        $this->assertPublicPhase($player);
        $this->getRoundData()->getTileAreas()->kongByOther($player, $this->getCurrentPlayer());

        if (!$this->handleFourKongDraw()) {
            // switch phase
//            $this->getRoundData()->toPrivatePhase($player, false);
            $this->getRoundData()->getPhaseState()->setCustomNextState(
                new PrivatePhaseState($player, false)
            );
            $this->getRoundData()->toNextPhase();
        }
    }

    function plusKongByOther(Player $player) {
        $this->assertPublicPhase($player);
        $this->getRoundData()->getTileAreas()->plusKongByOther($player, $this->getCurrentPlayer());

        if (!$this->handleFourKongDraw()) {
            // switch phase
//            $this->getRoundData()->toPrivatePhase($player, false);
            $this->getRoundData()->getPhaseState()->setCustomNextState(
                new PrivatePhaseState($player, false)
            );
            $this->getRoundData()->toNextPhase();
        }
    }

    function winByOther(Player $player) {
        $this->assertPublicPhase($player);
        // do
        $result = WinRoundResult::createWinByOther($this->getPlayerList()->toArray(), $player, $this->getWinResult($player), $this->getCurrentPlayer(),
            $this->getRoundData()->getTileAreas()->getAccumulatedReachCount(), $this->getRoundData()->getRoundWindData()->getSelfWindTurn());
        // phase
        $this->getRoundData()->toNextPhase(new OverPhaseState($result));
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
        $result = WinRoundResult::createMultiWinByOther(
            $this->getPlayerList()->toArray(), $players, $winResults, $this->getCurrentPlayer(),
            $this->getRoundData()->getTileAreas()->getAccumulatedReachCount(),
            $this->getRoundData()->getRoundWindData()->getSelfWindTurn());
        // phase
        $this->getRoundData()->toNextPhase(new OverPhaseState($result));
    }

    protected function assertPublicPhase($player = null) {
        $valid = $this->getRoundPhase() == RoundPhase::getPublicInstance() && ($player != $this->getCurrentPlayer());
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
    }
}