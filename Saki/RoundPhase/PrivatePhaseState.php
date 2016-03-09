<?php
namespace Saki\RoundPhase;

use Saki\Game\Player;
use Saki\Game\RoundData;
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

    function getDefaultNextState(RoundData $roundData) {
        return new PublicPhaseState();
    }

    function enter(RoundData $roundData) {
        if (!$this->isCurrentPlayer()) {
            $roundData->getTurnManager()->toPlayer($this->getPlayer());
        }

        if ($this->shouldDrawTile()) {
            $roundData->getTileAreas()->draw($this->getPlayer());
        }
    }

    function leave(RoundData $roundData) {
        // do nothing
    }
}