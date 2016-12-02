<?php
namespace Saki\Command;
use Saki\Game\SeatWind;
use Saki\Util\ArrayList;
use Saki\Util\Utils;

/**
 * @package Saki\Command
 */
class CommandProvided {
    private $actorCommands;

    /**
     * @param ArrayList[] $actorCommands ['E' => ArrayList(...), ...]
     */
    function __construct(array $actorCommands) {
        $this->actorCommands = $actorCommands;
    }

    /**
     * @param SeatWind $actor
     * @param string $commandClass
     * @return ArrayList
     */
    function getActorProvided(SeatWind $actor, string $commandClass = null) {
        $commandList = $this->actorCommands[$actor->__toString()];
        return $commandList
            ->getCopy()
            ->where(Utils::toClassPredicate($commandClass));
    }
}