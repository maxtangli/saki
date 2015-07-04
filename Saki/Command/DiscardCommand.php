<?php
namespace Saki\Command;

use Saki\Game\Player;
use Saki\Game\Round;
use Saki\Tile\Tile;

class DiscardCommand extends Command {
    static function fromString(Round $round, Player $player, $stringTokens) {
        $tileString = $stringTokens[0];
        return new self($round, $player, Tile::fromString($tileString));
    }

    private $tile;

    function __construct(Round $round, Player $player, Tile $tile) {
        parent::__construct($round, $player);
        $this->tile = $tile;
    }

    function __toString() {
        return parent::__toString() . " {$this->getTile()}";
    }

    function getTile() {
        return $this->tile;
    }

    function execute() {
        $this->getRound()->discard($this->getPlayer(), $this->getTile());
    }
}