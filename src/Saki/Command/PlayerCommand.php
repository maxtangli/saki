<?php
namespace Saki\Command;

use Saki\Game\Area;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Util\ArrayList;

/**
 * @package Saki\Command
 */
abstract class PlayerCommand extends Command {
    //region subclass hooks
    /**
     * @param Round $round
     * @param SeatWind $actor
     * @return PlayerCommand[]
     */
    static function getExecutables(Round $round, SeatWind $actor) {
        $actorArea = $round->getArea($actor);
        if (!$actorArea->isActor()) {
            return [];
        }

        $executableList = static::getExecutableListImpl($round, $actor, $actorArea);
        return $executableList->toArray();
    }

    /**
     * @param Round $round
     * @param SeatWind $actor
     * @param Area $actorArea
     * @return ArrayList
     */
    protected static function getExecutableListImpl(Round $round, SeatWind $actor, Area $actorArea) {
        return new ArrayList();
    }

    /**
     * @param Round $round
     * @param SeatWind $actor
     * @param ArrayList $otherParamsList
     * @return ArrayList
     */
    protected static function createMany(Round $round, SeatWind $actor, ArrayList $otherParamsList) {
        $toCommand = function ($otherParams) use ($round, $actor) {
            $actualParams = is_array($otherParams) ? $otherParams : [$otherParams];
            array_unshift($actualParams, $actor);
            return new static($round, $actualParams);
        };
        return $otherParamsList->toArrayList($toCommand);
    }
    //endregion

    /**
     * @param Round $round
     * @param array $params
     */
    function __construct(Round $round, array $params) {
        $valid = !empty($params) && $params[0] instanceof SeatWind;
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

    //region subclass hooks
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