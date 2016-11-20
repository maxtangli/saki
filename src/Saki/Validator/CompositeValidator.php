<?php
namespace Saki\Validation;
use Saki\Game\Area;
use Saki\Game\Round;

/**
 * @package Saki\Validation
 */
class CompositeValidator extends Validator {
    private $validations;

    /**
     * @param Validator[] $validations
     */
    function __construct(array $validations) {
        $this->validations = $validations;
    }

    /**
     * @param Validator $validation
     */
    function add(Validator $validation) {
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