<?php
namespace Saki\Game;

use Saki\RoundResult\RoundResult;
use Saki\Tile\Tile;
use Saki\Util\Roller;

class TurnManager {
    /**
     * @var PlayerList immutable
     */
    private $playerList;

    /**
     * @var Roller
     */
    private $playerWindRoller;
    /**
     * @var RoundPhase
     */
    private $roundPhase;
    /**
     * @var RoundResult
     */
    private $roundResult;

    function __construct(PlayerList $playerList) {
        $windTiles = Tile::getWindTiles($playerList->count()); // valid check

        $this->playerList = $playerList;

        $this->playerWindRoller = new Roller($windTiles);
        $this->roundPhase = RoundPhase::getInstance(RoundPhase::INIT_PHASE);
        $this->roundResult = null;
    }

    function reset() {
        $this->playerWindRoller->reset(Tile::fromString('E'));
        $this->roundPhase = RoundPhase::getInstance(RoundPhase::INIT_PHASE);
        $this->roundResult = null;
    }

    function debugSet(Player $currentPlayer, RoundPhase $roundPhase, $globalTurn) {
        if ($this->roundResult !== null) {
            throw new \InvalidArgumentException('Not implemented.');
        }

        $this->playerWindRoller->debugSet($currentPlayer->getSelfWind(), $globalTurn);
        $this->roundPhase = $roundPhase;
    }

    function getRoundPhase() {
        return $this->roundPhase;
    }

    function getRoundResult() {
        if (!$this->roundResult) {
            throw new \LogicException();
        }
        return $this->roundResult;
    }

    function start() {
        if ($this->getRoundPhase()->getValue() != RoundPhase::INIT_PHASE) {
            throw new \InvalidArgumentException(
                sprintf('current phase[%s] is not allowed to start and switch to private phase.', $this->roundPhase)
            );
        }

        $this->roundPhase = RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE);
        // other data already initialized by new() or reset()
    }

    function toPublicPhase() {
        if ($this->getRoundPhase()->getValue() != RoundPhase::PRIVATE_PHASE) {
            throw new \InvalidArgumentException(
                sprintf('current phase[%s] is not allowed to switch to public phase.', $this->roundPhase)
            );
        }

        $this->roundPhase = RoundPhase::getInstance(RoundPhase::PUBLIC_PHASE);
    }

    function toPrivatePhase(Tile $playerWind) {
        if ($this->getRoundPhase()->getValue() != RoundPhase::PUBLIC_PHASE) {
            throw new \InvalidArgumentException(
                sprintf('current phase[%s] is not allowed to switch to private phase.', $this->roundPhase)
            );
        }

        $this->playerWindRoller->toTarget($playerWind);
        $this->roundPhase = RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE);
    }

    function over(RoundResult $roundResult) {
        if (!$this->mayToOverPhase()) {
            throw new \InvalidArgumentException(
                sprintf('current phase[%s] is not allowed to switch to over phase.', $this->roundPhase)
            );
        }
        $this->roundPhase = RoundPhase::getInstance(RoundPhase::OVER_PHASE);
        $this->roundResult = $roundResult;
    }

    protected function mayToOverPhase() {
        return $this->getRoundPhase()->isPrivateOrPublic();
    }

    // delegate methods of Roller

    function getGlobalTurn() {
        return $this->playerWindRoller->getGlobalTurn();
    }

    /**
     * @return Player
     */
    function getCurrentPlayer() {
        $wind = $this->playerWindRoller->getCurrentTarget();
        return $this->selfWindToPlayer($wind);
    }

    /**
     * @param $offset
     * @param Player $basePlayer
     * @return Player
     */
    function getOffsetPlayer($offset, Player $basePlayer = null) {
        $basePlayerWind = $basePlayer ? $basePlayer->getSelfWind(): null;
        $wind = $this->playerWindRoller->getOffsetTarget($offset, $basePlayerWind);
        return $this->selfWindToPlayer($wind);
    }

    protected function selfWindToPlayer(Tile $selfWind) {
        return $this->playerList->getSelfWindPlayer($selfWind);
    }
}