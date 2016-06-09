<?php
namespace Saki\Command;

use Saki\Game\Area;
use Saki\Game\SeatWind;

/**
 * @package Saki\Command
 */
abstract class PlayerCommand extends Command {
    //region subclass hooks
    static function getExecutables(CommandContext $context, SeatWind $actor) {
        return [];
    }

    //endregion

    /**
     * @param CommandContext $context
     * @param array $params
     */
    function __construct(CommandContext $context, array $params) {
        $valid = count($params) > 0 && $params[0] instanceof SeatWind;
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        parent::__construct($context, $params);
    }

    /**
     * @return SeatWind
     */
    function getActor() {
        return $this->getParam(0);
    }

    /**
     * @return Area
     */
    function getActorArea() {
        return $this->getContext()->getAreas()->getArea($this->getActor());
    }

    //region override Command
    protected function executableImpl(CommandContext $context) {
        $actorArea = $this->getActorArea();
        return $this->matchPhase($context, $actorArea)
        && $this->matchActor($context, $actorArea)
        && $this->matchOther($context, $actorArea);
    }

    protected function executeImpl(CommandContext $context) {
        $actorArea = $this->getActorArea();
        return $this->executePlayerImpl($context, $actorArea);
    }
    //endregion

    //region subclass hooks
    /**
     * @param CommandContext $context
     * @param Area $actorArea
     * @return bool
     */
    abstract protected function matchPhase(CommandContext $context, Area $actorArea);

    /**
     * @param CommandContext $context
     * @param Area $actorArea
     * @return bool
     */
    abstract protected function matchActor(CommandContext $context, Area $actorArea);

    /**
     * @param CommandContext $context
     * @param Area $actorArea
     * @return bool
     */
    abstract protected function matchOther(CommandContext $context, Area $actorArea);

    /**
     * @param CommandContext $context
     * @param Area $actorArea
     */
    abstract protected function executePlayerImpl(CommandContext $context, Area $actorArea);
    //endregion
}