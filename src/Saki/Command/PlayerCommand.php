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
    //region getExecutableList
    /**
     * @param Round $round
     * @param SeatWind $actor
     * @return ArrayList ArrayList of PlayerCommand.
     */
    static function getExecutableList(Round $round, SeatWind $actor) {
        if (!static::matchPhaseAndActor($round, $actor)) {
            return new ArrayList();
        }

        return static::getExecutableListImpl($round, $actor, $round->getArea($actor));
    }

    /**
     * @param Round $round
     * @param SeatWind $actor
     * @return bool
     */
    static function matchPhaseAndActor(Round $round, SeatWind $actor) {
        $class = get_called_class();
        $phase = $round->getPhase();
        $validPhase = (is_subclass_of($class, PrivateCommand::class)  && $phase->isPrivate())
            || (is_subclass_of($class, PublicCommand::class) && $phase->isPublic());

        $isPhaseActor = $round->getArea($actor)->isPhaseActor();

        return $validPhase && $isPhaseActor;
    }

    /**
     * @param Round $round
     * @param SeatWind $actor
     * @param Area $actorArea
     * @return ArrayList
     */
    abstract protected static function getExecutableListImpl(Round $round, SeatWind $actor, Area $actorArea);

    /**
     * @param Round $round
     * @param SeatWind $actor
     * @param ArrayList $otherParamsList
     * @param bool $validate
     * @return ArrayList
     */
    protected static function createMany(Round $round, SeatWind $actor,
                                         ArrayList $otherParamsList, bool $validate = false) {
        $toCommand = function ($otherParams) use ($round, $actor) {
            $actualParams = is_array($otherParams) ? $otherParams : [$otherParams];
            array_unshift($actualParams, $actor);
            return new static($round, $actualParams);
        };
        $commandList = $otherParamsList->toArrayList($toCommand);

        // todo remove
        if ($validate == false) {
            throw new \InvalidArgumentException();
        }

        if ($validate) {
            $executable = function (Command $command) {
                return $command->executable();
            };
            $commandList = $commandList->where($executable);
        }

        return $commandList;
    }
    //endregion

    //region constructor, getter
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
    //endregion

    //region override Command
    protected function executableImpl(Round $round) {
        if (!static::matchPhaseAndActor($round, $this->getActor())) {
            return false;
        }

        $actorArea = $this->getActorArea();
        $matches = [
//            [$this, 'matchPhase'],
//            [$this, 'matchActor'],
            [$this, 'matchOther'],
            [$this, 'matchProvider'],
        ];
        foreach ($matches as $match) {
            $matchResult = call_user_func($match, $round, $actorArea);
            if ($matchResult !== true) {
                $name = $match[1];
                return new InvalidCommandException($this, "$name failed");
            }
        }

        return true;
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
     */
    abstract protected function matchOther(Round $round, Area $actorArea);

    /**
     * @param Round $round
     * @param Area $actorArea
     * @return bool
     */
    protected function matchProvider(Round $round, Area $actorArea) {
        return true;
    }

    /**
     * @param Round $round
     * @param Area $actorArea
     * @return
     */
    abstract protected function executePlayerImpl(Round $round, Area $actorArea);
    //endregion
}