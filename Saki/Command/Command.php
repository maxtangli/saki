<?php
namespace Saki\Command;

use Saki\Game\Round;
use Saki\Util\Utils;

abstract class CommandParam {

}

abstract class CommandPlayerParam {

}

abstract class Command {
    /**
     * @return CommandParam[] CommandParam instances in order of constructor declarations
     */
    abstract static function getParamTypes();

    function __toString() {
        $commandToken = Utils::str_class_last_part(get_called_class(), 'Command');
        return lcfirst($commandToken);
    }

    abstract function execute();
}

class DiscardCommand extends Command {
    private $tile;

    function __construct(Round $round, Player $player, Tile $tile) {
        parent::__construct($round, $player);
        $this->tile = $tile;
    }

    function __toString() {
        return parent::__toString() . " {$this->getTile()}";
    }
}
