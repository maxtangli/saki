<?php
namespace Saki\Command;

use Saki\Game\Area;
use Saki\Game\Round;
use Saki\Game\SeatWind;

/**
 * @package Saki\Command
 */
abstract class PlayerCommand extends Command {
    //region subuse Saki\Game\Round; class hooks
    static function getExecutables(Round $round, SeatWind $actor) {
        return [];
    }

    //endregion

    /**
     * @param Round $round
     * @param array $params
     */
    function __construct(Round $round, array $params) {
        $valid = count($params) > 0 && $params[0] instanceof SeatWind;
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        parent::__construct($round, $params);
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
        return $this->getRound()->getArea($this->getActor());
    }

    //region override Command
    protected function executableImpl(Round $round) {
        $actorArea = $this->getActorArea();
        return $this->matchPhase($round, $actorArea)
        && $this->matchActor($round, $actorArea)
        && $this->matchOther($round, $actorArea);
    }

    protected function executeImpl(Round $round) {
        $actorArea = $this->getActorArea();
        return $this->executePlayerImpl($round, $actorArea);
    }
    //endregion

    //region subuse Saki\Game\Round; class hooks
    /**
     * @param Round $round
     * @param Area $actorArea
     * @return bool
     * 
     */
    abstract protected function matchPhase(Round $round, Area $actorArea);

    /**
     * @param Round $round
     * @param Area $actorArea
     * @return bool
     * 
     */
    abstract protected function matchActor(Round $round, Area $actorArea);

    /**
     * @param Round $round
     * @param Area $actorArea
     * @return bool
     * 
     */
    abstract protected function matchOther(Round $round, Area $actorArea);

    /**
     * @param Round $round
     * @param Area $actorArea
     * @return
     * 
     */
    abstract protected function executePlayerImpl(Round $round, Area $actorArea);
    //endregion
}