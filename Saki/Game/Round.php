<?php

namespace Saki\Game;

use Saki\FinalScore\FinalScoreStrategyTarget;
use Saki\Game\Result\ExhaustiveDrawResult;
use Saki\Game\Result\GameResult;
use Saki\Game\Result\RoundResult;
use Saki\Game\Result\WinBySelfRoundResult;
use Saki\Tile\Tile;
use Saki\Win\WinAnalyzer;
use Saki\Win\WinAnalyzerTarget;
use Saki\Win\WinState;

class Round {
    private $roundData;
    private $roundResult;
    private $roundPhase;
    private $yakuAnalyzer;

    function __construct(RoundData $roundData = null) {
        $this->roundData = $roundData !== null ? $roundData : new RoundData();
        $this->roundResult = null;
        $this->roundPhase = null;
        $this->yakuAnalyzer = new WinAnalyzer();
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
        return $this->roundPhase;
    }

    protected function setRoundPhase(RoundPhase $roundPhase) {
        $this->roundPhase = $roundPhase;
    }

    function getYakuAnalyzer() {
        return $this->yakuAnalyzer;
    }

    /**
     * @return Wall
     */
    function getWall() {
        return $this->getRoundData()->getWall();
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
    function getDealerPlayer() {
        return $this->getPlayerList()->getDealerPlayer();
    }

    /**
     * @return Player
     */
    function getPrevPlayer() {
        return $this->getPlayerList()->getPrevPlayer();
    }

    /**
     * @return Player
     */
    function getCurrentPlayer() {
        return $this->getPlayerList()->getCurrentPlayer();
    }

    /**
     * @return Player
     */
    function getNextPlayer() {
        return $this->getPlayerList()->getNextPlayer();
    }

    /**
     * @param Player $player
     * @param bool $addTurn
     */
    protected function setCurrentPlayer(Player $player, $addTurn) {
        $this->getPlayerList()->toPlayer($player, $addTurn);
    }

    protected function toInitPhase() {
        $this->roundPhase = RoundPhase::getInstance(RoundPhase::INIT_PHASE);

        // each player draw initial tiles
        $playerCount = $this->getPlayerList()->count();
        $drawTileCounts = [4, 4, 4, 1];
        foreach ($drawTileCounts as $drawTileCount) {
            for ($cnt = 0; $cnt < $playerCount; ++$cnt) {
                $this->drawInit($this->getCurrentPlayer(), $drawTileCount);
                $this->setCurrentPlayer($this->getNextPlayer(), false);
            }
        }

        // go to dealer player's private phase
        $this->toPrivatePhase($this->getDealerPlayer(), true);
    }

    /**
     * @param Player $player
     * @param bool $drawTile
     */
    protected function toPrivatePhase(Player $player, $drawTile) {
        if ($this->getWall()->getRemainTileCount() == 0 && $drawTile) {
            // exhaustive draw
            $players = $this->getPlayerList()->toArray();
            $analyzer = $this->getYakuAnalyzer();
            $roundData = $this->getRoundData();
            $isWaitingStates = array_map(function (Player $player) use ($analyzer, $roundData) {
                $target = new WinAnalyzerTarget($player, $roundData);
                return $analyzer->isWaiting($target);
            }, $players);
            $result = new ExhaustiveDrawResult($players, $isWaitingStates);
            $this->toOverPhase($result);
        } else {
            $this->setRoundPhase(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE));
            $this->setCurrentPlayer($player, true);
            if ($drawTile) {
                $this->draw($player);
            }
        }
    }

    protected function toPublicPhase() {
        $this->setRoundPhase(RoundPhase::getInstance(RoundPhase::PUBLIC_PHASE));
    }

    protected function toOverPhase(RoundResult $result) {
        $this->setRoundPhase(RoundPhase::getInstance(RoundPhase::OVER_PHASE));
        // save result
        $this->roundResult = $result;
        // modify scores
        foreach ($this->getPlayerList() as $player) {
            $afterScore = $result->getScoreDelta($player)->getAfter();
            $player->setScore($afterScore);
        }
        // clear accumulatedReachCount if isWin
        if ($result->isWin()) {
            $this->getRoundData()->setAccumulatedReachCount(0);
        }
    }

    function isGameOver() {
        $isOverPhase = $this->getRoundPhase() == RoundPhase::getOverPhaseInstance();
        if (!$isOverPhase) {
            return false;
        }

        $roundData = $this->getRoundData();
        if ($roundData->hasMinusScorePlayer()) { // 有玩家被打飞，游戏结束
            return true;
        } elseif ($roundData->isLastNorthRoundWindTurn()) { // 北入终局，游戏结束
            return true;
        } elseif (!$roundData->isLastOrExtraRoundWindTurn()) { // 指定场数未达，游戏未结束
            return false;
        } else { // 达到指定场数
            $topPlayer = $this->getPlayerList()->getTopPlayer();
            $isTopPlayerEnoughScore = $topPlayer->getScore() >= 30000; // todo rule
            if (!$isTopPlayerEnoughScore) { // 若首位点数未达原点，游戏未结束
                return false;
            } else { // 首位点数达到原点，非连庄 或 连庄者达首位，游戏结束
                $keepDealer = $this->getRoundResult()->isKeepDealer();
                $dealerIsTopPlayer = $this->getDealerPlayer() == $topPlayer;
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

    protected function drawInit(Player $player, $drawTileCount) {
        $player->getPlayerArea()->drawInit($this->getWall()->pop($drawTileCount));
    }

    protected function draw(Player $player) {
        $player->getPlayerArea()->draw($this->getWall()->pop());
    }

    protected function drawReplacement(Player $player) {
        $player->getPlayerArea()->draw($this->getWall()->shift());
    }

    function discard(Player $player, Tile $selfTile) {
        // valid: private phase, currentPlayer
        $valid = $this->getRoundPhase()->getValue() == RoundPhase::PRIVATE_PHASE && $player == $this->getCurrentPlayer();
        if (!$valid) {
            throw new \InvalidArgumentException(sprintf('[%s], [%s], [%s]', $this->getRoundPhase(), $player, $this->getCurrentPlayer()));
        }
        // do
        $player->getPlayerArea()->discard($selfTile);
        // switch phase
        $this->toPublicPhase();
    }

    function reach(Player $player, Tile $selfTile) {
        // valid: private phase, currentPlayer
        $valid = $this->getRoundPhase()->getValue() == RoundPhase::PRIVATE_PHASE && $player == $this->getCurrentPlayer();
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        // valid: reach condition
        /**
         * https://ja.wikipedia.org/wiki/%E7%AB%8B%E7%9B%B4
         * 条件
         * - 立直していないこと。
         * - 聴牌していること。
         * - 門前であること。すなわち、チー、ポン、明槓をしていないこと。
         * - トビ有りのルールならば、点棒を最低でも1000点持っていること。つまり立直棒として1000点を供託したときにハコを割ってしまうような場合、立直はできない。供託時にちょうど0点になる場合、認められる場合と認められない場合がある。トビ無しの場合にハコを割っていた場合も、点棒を借りてリーチをかけることを認める場合と認めない場合がある。
         * - 壁牌（山）の残りが王牌を除いて4枚（三人麻雀では3枚）以上あること。すなわち立直を宣言した後で少なくとも1回の自摸が残されているということ。ただし、鳴きや暗槓が入って結果的に自摸の機会なく流局したとしてもペナルティはない。
         * - 4人全員が立直をかけた場合、四家立直として流局となる（四家立直による途中流局を認めないルールもあり、その場合は続行される）。
         */
        $notReachYet = !$player->getPlayerArea()->isReach();
        if (!$notReachYet) {
            throw new \InvalidArgumentException('Reach condition violated: not reach yet.');
        }

        $analyzer = $this->getYakuAnalyzer();
        $target = new WinAnalyzerTarget($player, $this->getRoundData());
        $isWaiting = $analyzer->isWaiting($target);
        if (!$isWaiting) {
            throw new \InvalidArgumentException('Reach condition violated: is waiting.');
        }

        $isConcealed = $target->isConcealed();
        if (!$isConcealed) {
            throw new \InvalidArgumentException('Reach condition violated: is concealed.');
        }

        $enoughScore = $player->getScore() >= 1000;
        if (!$enoughScore) {
            throw new \InvalidArgumentException('Reach condition violated: at least 1000 score.');
        }

        $hasDrawTileChance = $this->getWall()->getRemainTileCount() >= 4;
        if (!$hasDrawTileChance) {
            throw new \InvalidArgumentException('Reach condition violated: at least 1 draw tile chance.');
        }

        // todo four reach draw

        // do
        $player->getPlayerArea()->reach($selfTile);
        // switch phase
        $this->toPublicPhase();
    }

    function kongBySelf(Player $player, Tile $selfTile) {
        // valid: private phase, currentPlayer
        $valid = $this->getRoundPhase()->getValue() == RoundPhase::PRIVATE_PHASE && $player == $this->getCurrentPlayer();
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        // do
        $player->getPlayerArea()->kongBySelf($selfTile);
        $this->drawReplacement($player);
        // stay in private phase
    }

    function plusKongBySelf(Player $player, Tile $selfTile) {
        // valid: private phase, currentPlayer
        $valid = $this->getRoundPhase()->getValue() == RoundPhase::PRIVATE_PHASE && $player == $this->getCurrentPlayer();
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        // do
        $player->getPlayerArea()->plusKongBySelf($selfTile);
        $this->drawReplacement($player);
        // stay in private phase
    }

    function winBySelf(Player $player) {
        /// private phase, currentPlayer
        $valid = $this->getRoundPhase()->getValue() == RoundPhase::PRIVATE_PHASE && $player == $this->getCurrentPlayer();
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        // do
        $analyzer = $this->getYakuAnalyzer();
        $target = new WinAnalyzerTarget($player, $this->getRoundData());
        $winResult = $analyzer->analyzeTarget($target);
        if ($winResult->getWinState() != WinState::getWinInstance()) {
            throw new \InvalidArgumentException();
        }
        $roundResult = new WinBySelfRoundResult($this->getPlayerList()->toArray(), $player, $winResult,
            $this->getRoundData()->getAccumulatedReachCount(), $this->getRoundData()->getSelfWindTurn());
        // phase
        $this->toOverPhase($roundResult);
    }

    function passPublicPhase() {
        $valid = $this->getRoundPhase()->getValue() == RoundPhase::PUBLIC_PHASE;
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        $this->toPrivatePhase($this->getCurrentPlayer(), true);
    }

    function chowByOther(Player $player, Tile $tile1, Tile $tile2) {
        // valid: public phase, next player
        $valid = $this->getRoundPhase()->getValue() == RoundPhase::PUBLIC_PHASE && $player == $this->getNextPlayer();
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        // execute
        $currentPlayerArea = $this->getCurrentPlayer()->getPlayerArea();
        $playerArea = $player->getPlayerArea();
        $targetTile = $currentPlayerArea->getDiscardedTileList()->getLast(); // test valid
        $playerArea->chowByOther($targetTile, $tile1, $tile2); // test valid
        $currentPlayerArea->getDiscardedTileList()->pop();
        // switch phase
        $this->toPrivatePhase($player, false);
    }

    function pongByOther(Player $player) {
        // valid: public phase, non-current player
        $valid = $this->getRoundPhase()->getValue() == RoundPhase::PUBLIC_PHASE && $player != $this->getCurrentPlayer();
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        // execute
        $currentPlayerArea = $this->getCurrentPlayer()->getPlayerArea();
        $playerArea = $player->getPlayerArea();
        $targetTile = $currentPlayerArea->getDiscardedTileList()->getLast(); // test valid
        $playerArea->pongByOther($targetTile); // test valid
        $currentPlayerArea->getDiscardedTileList()->pop();
        // switch phase
        $this->toPrivatePhase($player, false);
    }

    function kongByOther(Player $player) {
        // valid: public phase, non-current player
        $valid = $this->getRoundPhase()->getValue() == RoundPhase::PUBLIC_PHASE && $player != $this->getCurrentPlayer();
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        // execute
        $currentPlayerArea = $this->getCurrentPlayer()->getPlayerArea();
        $playerArea = $player->getPlayerArea();
        $targetTile = $currentPlayerArea->getDiscardedTileList()->getLast(); // test valid
        $playerArea->kongByOther($targetTile); // test valid
        $this->drawReplacement($player);
        $currentPlayerArea->getDiscardedTileList()->pop();
        // switch phase
        $this->toPrivatePhase($player, false);
    }

    function plusKongByOther(Player $player) {
        // valid: public phase, non-current player
        $valid = $this->getRoundPhase()->getValue() == RoundPhase::PUBLIC_PHASE && $player != $this->getCurrentPlayer();
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        // execute
        $currentPlayerArea = $this->getCurrentPlayer()->getPlayerArea();
        $playerArea = $player->getPlayerArea();
        $targetTile = $currentPlayerArea->getDiscardedTileList()->getLast(); // test valid
        $playerArea->plusKongByOther($targetTile);
        $this->drawReplacement($player);
        $currentPlayerArea->getDiscardedTileList()->pop();
        // switch phase
        $this->toPrivatePhase($player, false);
    }

    function winByOther(Player $player) {
        // public

        // do

        // phase
    }

    function multipleWinByOther(array $players) {

    }
}

//
//class CommandProcessor {
//
//    private $publicPhaseCommandPoller;
//
//    function toPublicPhase() {
//        $this->getPublicPhaseCommandPoller()->init($this->getCandidateCommands());
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
//                    $candidateCommands[] = new DiscardCommand($this, $currentPlayer, $currentPlayerArea->getCandidateTile());
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