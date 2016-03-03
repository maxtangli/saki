<?php
namespace Saki\RoundPhase;

use Saki\Game\Player;
use Saki\Game\RoundData;
use Saki\Game\RoundPhase;

class PrivatePhaseState extends RoundPhaseState {
    private $player;
    private $shouldDrawTile;
    private $isFromInit; // todo remove

    function __construct(Player $player, bool $shouldDrawTile, $isFromInit = false) {
        $this->player = $player;
        $this->shouldDrawTile = $shouldDrawTile;
        $this->isFromInit = $isFromInit;
    }

    function getPlayer() {
        return $this->player;
    }

    function shouldDrawTile() {
        return $this->shouldDrawTile;
    }

    function getRoundPhase() {
        return RoundPhase::getPrivateInstance();
    }

    function getDefaultNextState(RoundData $roundData) {
        return new PublicPhaseState();
    }

    function enter(RoundData $roundData) {
        // todo handle exhaustive draw

        if (!$this->isFromInit) {
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