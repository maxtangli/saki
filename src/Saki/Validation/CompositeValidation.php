<?php
namespace Saki\Validation;
use Saki\Game\Area;
use Saki\Game\Round;

/**
 * @package Saki\Validation
 */
class CompositeValidation extends Validation {
    private $validations;

    /**
     * @param Validation[] $validations
     */
    function __construct(array $validations) {
        $this->validations = $validations;
    }

    /**
     * @param Validation $validation
     */
    function add(Validation $validation) {
        array_push($this->validations, $validation);
    }

    //region Validation impl
    function validate(Round $round, Area $actorArea = null) {
        foreach ($this->validations as $validation) {
            $validation->validate($round, $actorArea);
        }
    }
    //endregion
}