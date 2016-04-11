<?php
namespace Saki\Phase;

use Saki\Game\Phase;
use Saki\Game\Player;
use Saki\Game\Round;

class PrivatePhaseState extends PhaseState {
    private $player;
    private $shouldDrawTile;
    private $isCurrentPlayer;

    function __construct(Player $player, bool $shouldDrawTile, bool $isCurrentPlayer = false) {
        $this->player = $player;
        $this->shouldDrawTile = $shouldDrawTile;
        $this->isCurrentPlayer = $isCurrentPlayer;
    }

    function getPlayer() {
        return $this->player;
    }

    function shouldDrawTile() {
        return $this->shouldDrawTile;
    }

    function isCurrentPlayer() {
        return $this->isCurrentPlayer;
    }

    function getPhase() {
        return Phase::getPrivateInstance();
    }

    function getDefaultNextState(Round $round) {
        return new PublicPhaseState();
    }

    function enter(Round $round) {
        if (!$this->isCurrentPlayer()) {
            $round->getAreas()->toSeatWind($this->getPlayer()->getArea()->getSeatWind());
        }

        if ($this->shouldDrawTile()) {
            $round->getAreas()->draw($this->getPlayer());
        }
    }

    function leave(Round $round) {
        // do nothing
    }
}