<?php
namespace Saki\Game;

use Saki\RoundResult\RoundResult;
use Saki\Tile\Tile;
use Saki\Util\Roller;

class TurnManager {
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

    function __construct($playerCount) {
        $windTiles = Tile::getWindTiles($playerCount); // valid check

        $this->playerWindRoller = new Roller($windTiles);
        $this->roundPhase = RoundPhase::getInstance(RoundPhase::INIT_PHASE);
        $this->roundResult = null;
    }

    function reset() {
        $this->playerWindRoller->reset(Tile::fromString('E'));
        $this->roundPhase = RoundPhase::getInstance(RoundPhase::INIT_PHASE);
        $this->roundResult = null;
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

    function debugSetRoundPhase(RoundPhase $roundPhase) {
        $this->roundPhase = $roundPhase;
    }

    // delegate methods of Roller


}