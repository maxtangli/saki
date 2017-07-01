<?php
namespace Saki\Command;
use Saki\Command\PublicCommand\PassCommand;
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
     * @return string
     */
    function __toString() {
        return implode(';', $this->actorCommands);
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

    /**
     * @return ArrayList
     */
    function getTrivialPassList() {
        $result = new ArrayList();
        foreach ($this->actorCommands as $commandList) {
            if ($commandList->count() == 1
                && $commandList->getFirst() instanceof PassCommand) {
                $result->insertLast($commandList->getFirst());
            }
        }
        return $result;
    }
}