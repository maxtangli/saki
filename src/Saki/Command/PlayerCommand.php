<?php
namespace Saki\Command;

use Saki\Command\PrivateCommand\PrivateCommand;
use Saki\Command\PublicCommand\PublicCommand;
use Saki\Game\Area;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Util\ArrayList;

/**
 * @package Saki\Command
 */
abstract class PlayerCommand extends Command {
    //region CommandProvider helper
    /**
     * @param Round $round
     * @param SeatWind $actor
     * @param Area $actorArea
     * @return ArrayList
     */
    abstract static function getOtherParamsListRaw(Round $round, SeatWind $actor, Area $actorArea);
    //endregion

    //region constructor, getter
    /**
     * @param Round $round
     * @param SeatWind $actor
     * @return bool
     */
    static function matchPhaseAndActor(Round $round, SeatWind $actor) {
        $class = get_called_class();
        $phase = $round->getPhase();
        $validPhase = (is_subclass_of($class, PrivateCommand::class) && $phase->isPrivate())
            || (is_subclass_of($class, PublicCommand::class) && $phase->isPublic());

        $isPhaseActor = $round->getArea($actor)->isPhaseActor();

        return $validPhase && $isPhaseActor;
    }

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

    //region Command impl
    final protected function executableImpl(Round $round) {
        if (!static::matchPhaseAndActor($round, $this->getActor())) {
            return false;
        }

        if ($round->getPhase()->isPublic()) {
            $decider = $round->getPhaseState()->getCommandDecider();
            $validDecider = $decider->allowSubmit($this) || $decider->isDecidedCommand($this);
            if (!$validDecider) {
                return false;
            }
        }

        return $this->executablePlayerImpl($round, $this->getActorArea());
    }

    final protected function executeImpl(Round $round) {
        if ($round->getPhase()->isPublic()) {
            $decider = $round->getPhaseState()->getCommandDecider();

            if ($decider->allowSubmit($this)) {
                $decider->submit($this);
            }

            if ($decider->decided()) {
                if ($decider->isDecidedCommand($this)) {
                    $decider->clear();
                    $this->executePlayerImpl($round, $this->getActorArea());
                } else {
                    $decider->getDecided()->execute();
                }
            } else {
                // do nothing
            }
        } else {
            $this->executePlayerImpl($round, $this->getActorArea());
        }
    }
    //endregion

    //region subclass hooks
    /**
     * @param Round $round
     * @param Area $actorArea
     * @return bool
     */
    abstract protected function executablePlayerImpl(Round $round, Area $actorArea);

    /**
     * @param Round $round
     * @param Area $actorArea
     */
    abstract protected function executePlayerImpl(Round $round, Area $actorArea);
    //endregion
}