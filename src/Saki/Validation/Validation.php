<?php

namespace Saki\Validation;

use Saki\Game\Area;
use Saki\Game\Round;

/**
 * @package Saki\Validation
 */
abstract class Validation {
    /**
     * @param Round $round
     * @param Area|null $actorArea
     * @return bool
     */
    function valid(Round $round, Area $actorArea = null) {
        try {
            $this->validate($round, $actorArea);
        } catch (ValidateException $e) {
            return false;
        }
        return true;
    }

    /**
     * @param Round $round
     * @param Area|null $actorArea
     * @return null
     */
    abstract function validate(Round $round, Area $actorArea = null);
}