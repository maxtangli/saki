<?php

namespace Saki\Game;

use Saki\Tile;
use Saki\Util\Enum;
use Saki\Command\Command;
use Saki\Command\DiscardCommand;

class RoundPhase extends Enum {
    const INIT_PHASE = 1;
    const PRIVATE_PHASE = 2;
    const PUBLIC_PHASE = 3;
    const OVER_PHASE = 4;

    static function getValue2StringMap() {
        return [
            self::INIT_PHASE => 'init phase',
            self::PRIVATE_PHASE => 'private phase',
            self::PUBLIC_PHASE => 'public phase',
            self::OVER_PHASE => 'over phase',
        ];
    }

    /**
     * @param $value
     * @return RoundPhase
     */
    static function getInstance($value) {
        return parent::getInstance($value);
    }

    /**
     * @param string $s
     * @return RoundPhase
     */
    static function fromString($s) {
        return parent::fromString($s);
    }


}

class ZimoCommand extends Command {
    function execute() {

    }
}

class PassCommand extends Command {
    function execute() {

    }
}

class ChowCommand extends Command {
    function execute() {

    }
}

class PongCommand extends Command {
    function execute() {

    }
}

class KangCommand extends Command {
    function execute() {

    }
}

class RonCommand extends Command {
    function execute() {

    }
}

class Round {
    private $wall;
    private $playerList;
    private $dealerPlayer;

    private $playerAreas;
    private $turnManager;
    private $roundPhase;
    private $publicPhaseCommandPoller;

    function __construct(Wall $wall, PlayerList $playerList, $dealerPlayer) {
        $this->wall = $wall;
        $this->playerList = $playerList;
        $this->dealerPlayer = $dealerPlayer;

        $this->toInitPhase();
    }

    /**
     * @return Wall
     */
    function getWall() {
        return $this->wall;
    }

    /**
     * @return PlayerList
     */
    function getPlayerList() {
        return $this->playerList;
    }

    /**
     * @return Player
     */
    function getDealerPlayer() {
        return $this->dealerPlayer;
    }

    /**
     * @return PlayerArea[]
     */
    function getPlayerAreas() {
        return $this->playerAreas;
    }

    /**
     * @return TurnManager
     */
    protected function getTurnManager() {
        return $this->turnManager;
    }

    /**
     * @return Player
     */
    function getCurrentPlayer() {
        return $this->getTurnManager()->getCurrentPlayer();
    }

    /**
     * @return Player
     */
    function getNextPlayer() {
        return $this->getTurnManager()->getNextPlayer();
    }

    /**
     * @param Player $player
     * @param bool $addTurn
     */
    function setCurrentPlayer(Player $player, $addTurn) {
        $this->getTurnManager()->toPlayer($player, $addTurn);
    }

    /**
     * @return int
     */
    function getCurrentTurn() {
        return $this->getTurnManager()->getCurrentTurn();
    }

    /**
     * @param $player
     * @return PlayerArea
     */
    function getPlayerArea($player) {
        return $this->getPlayerAreas()[$this->getPlayerList()->valueToIndex($player)];
    }

    /**
     * @return PlayerArea
     */
    function getCurrentPlayerArea() {
        return $this->getPlayerArea($this->getCurrentPlayer());
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

    protected function getPublicPhaseCommandPoller() {
        if ($this->publicPhaseCommandPoller === null) {
            $this->publicPhaseCommandPoller = new PublicPhaseCommandPoller([]);
        }
        return $this->publicPhaseCommandPoller;
    }

    protected function toInitPhase() {
        $this->roundPhase = RoundPhase::getInstance(RoundPhase::INIT_PHASE);

        // init fields
        $this->playerAreas = array_map(function () {
            return new PlayerArea();
        }, range(1, count($this->getPlayerList())));
        $this->turnManager = new TurnManager($this->getPlayerList()->toArray(), $this->getDealerPlayer());

        // each player draw 4*4 tiles
        $playerCount = count($this->getPlayerList());
        for ($i = 0; $i < 4; ++$i) {
            for ($cnt = 0; $cnt < $playerCount; ++$cnt) {
                $this->getCurrentPlayerArea()->getOnHandTileSortedList()->push($this->getWall()->pop(4));
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
            $this->toOverPhase();
        } else {
            $this->setRoundPhase(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE));
            $this->setCurrentPlayer($player, true);
            if ($drawTile) {
                $this->getPlayerArea($this->getCurrentPlayer())->setCandidateTile($this->getWall()->pop());
            }
        }
    }

    protected function toPublicPhase() {
        $this->setRoundPhase(RoundPhase::getInstance(RoundPhase::PUBLIC_PHASE));
        $this->getPublicPhaseCommandPoller()->init($this->getCandidateCommands());
        $this->wonderIfPollerDecided();
    }

    protected function wonderIfPollerDecided() {
        $poller = $this->getPublicPhaseCommandPoller();;
        if ($poller->decided()) {
            $todoCommands = $poller->getDecidedCommands();
            if (!empty($todoCommands)) {
                foreach ($todoCommands as $todoCommand) {
                    $todoCommand->execute();
                }
            } else { // no decided commands
                $this->toPrivatePhase($this->getNextPlayer(), true);
            }
        } else { // candidate commands exist
            // waiting commands decided
        }
    }

    protected function toOverPhase() {
        $this->setRoundPhase(RoundPhase::getInstance(RoundPhase::OVER_PHASE));
        // todo draw/win
    }

    function acceptCommand(Command $command) {
        switch ($this->getRoundPhase()->getValue()) {
            case RoundPhase::PRIVATE_PHASE:
                $command->execute();
                break;
            case RoundPhase::PUBLIC_PHASE:
                $this->getPublicPhaseCommandPoller()->acceptCommand($command);
                $this->wonderIfPollerDecided();
                break;
            default:
                throw new \LogicException();
        }
    }

    function discard(Player $player, Tile $tile) {
        // private phase, currentPlayer
        $valid = $this->getRoundPhase()->getValue() == RoundPhase::PRIVATE_PHASE && $player == $this->getCurrentPlayer();
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        // do
        $this->getCurrentPlayerArea()->discard($tile);
        // phase
        $this->toPublicPhase();
    }


    function concealedKang(Player $player) {
        // private phase, currentPlayer

        // do

        // stay in private phase
    }

    function plusKang(Player $player, Tile $tile) {
        // private phase, currentPlayer

        // do

        // stay in private phase
    }

    function ronOnSelfDraw(Player $player) {
        /// private phase, currentPlayer

        // do

        // phase
        $this->toOverPhase();
    }

    function chow(Player $player, Tile $tile1, Tile $tile2) {
        // public
        $valid = $this->getRoundPhase()->getValue() == RoundPhase::PUBLIC_PHASE && $player == $this->getTurnManager();
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        // do

        // phase
    }

    function pong(Player $player, Tile $tile1, Tile $tile2) {
        // public
    }

    function exposedKang(Player $player) {
        // public

        // do

        // phase
    }

    function ronOnDiscard(Player $player) {
        // public

        // do

        // phase
        $this->toOverPhase();
    }

    /**
     * @return Command[]
     */
    function getCandidateCommands() {
        $candidateCommands = [];

        switch ($this->getRoundPhase()->getValue()) {
            case RoundPhase::PRIVATE_PHASE:
                $currentPlayer = $this->getCurrentPlayer();
                $currentPlayerArea = $this->getPlayerArea($currentPlayer);
                foreach ($currentPlayerArea->getOnHandTileSortedList() as $onHandTile) {
                    $candidateCommands[] = new DiscardCommand($this, $currentPlayer, $onHandTile);
                }
                if ($currentPlayerArea->hasCandidateTile()) {
                    $candidateCommands[] = new DiscardCommand($this, $currentPlayer, $currentPlayerArea->getCandidateTile());
                }
                $candidateCommands = array_unique($candidateCommands);
                break;
            case RoundPhase::PUBLIC_PHASE:
                break;
            case RoundPhase::OVER_PHASE:
                break;
            default:
                throw new \LogicException();
        }

        return $candidateCommands;
    }

    function getCandidateCommand(Player $player) {
        return array_values(array_filter($this->getCandidateCommands(), function (Command $v) use ($player) {
            return $v->getPlayer() == $player;
        }));
    }
}
