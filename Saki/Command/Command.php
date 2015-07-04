<?php
namespace Saki\Command;

use Saki\Game\Player;
use Saki\Game\Round;
use Saki\Util\Utils;

/**
 * A Command is meaningful only when bing with a Round
 * @package Saki\Game
 */
abstract class Command {
    private $round;
    private $player;

    static function fromString(Round $round, Player $player, $stringTokens) {
        return static::fromString($round, $player, $stringTokens);
    }

    function __construct(Round $round, Player $player) {
        $this->round = $round;
        $this->player = $player;
    }

    function __toString() {
        $commandToken = Utils::str_class_last_part(get_called_class(), 'Command');
        return lcfirst($commandToken) . ' ' . $this->getPlayer();
    }

    function getRound() {
        return $this->round;
    }

    function getPlayer() {
        return $this->player;
    }

    abstract function execute();
}