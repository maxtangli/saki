<?php
namespace Saki\RoundPhase;

use Saki\Game\Player;
use Saki\Game\Round;
use Saki\Game\RoundPhase;

class PrivatePhaseState extends RoundPhaseState {
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

    function getRoundPhase() {
        return RoundPhase::getPrivateInstance();
    }

    function getDefaultNextState(Round $round) {
        return new PublicPhaseState();
    }

    function enter(Round $round) {
        if (!$this->isCurrentPlayer()) {
            $round->getTurnManager()->toPlayer($this->getPlayer());
        }

        if ($this->shouldDrawTile()) {
            $round->getAreas()->draw($this->getPlayer());
        }
    }

    function leave(Round $round) {
        // do nothing
    }
}