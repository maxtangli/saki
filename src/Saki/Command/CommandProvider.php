<?php
namespace Saki\Command;

use Saki\Game\SeatWind;
use Saki\Util\ArrayList;

/**
 * @package Saki\Command
 */
class CommandProvider {
    private $context;
    private $provideCommandSet;

    /**
     * @param CommandContext $context
     * @param CommandSet $commandSet
     */
    function __construct(CommandContext $context, CommandSet $commandSet) {
        $this->context = $context;
        $this->provideCommandSet = $commandSet->toArrayList()->where(function (string $command) {
            return is_subclass_of($command, PlayerCommand::class)
            && !$command::isDebug();
        });
    }

    /**
     * @return CommandContext
     */
    function getContext() {
        return $this->context;
    }

    /**
     * @return ArrayList
     */
    function getProvideCommandSet() {
        return $this->provideCommandSet;
    }

    /**
     * @param SeatWind $actor
     * @return Command[]
     */
    function getExecutables(SeatWind $actor) {
        $context = $this->getContext();
        $getClassExecutables = function (string $class) use ($context, $actor) {
            return $class::getExecutables($context, $actor);
        };
        $executables = (new ArrayList())
            ->fromSelectMany($this->getProvideCommandSet(), $getClassExecutables)
            ->toArray();
        return $executables;
    }
}