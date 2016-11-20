<?php
namespace Saki\Validation;

use Saki\Game\Area;
use Saki\Game\Phase;
use Saki\Game\Round;
use Saki\Util\Enum;

/**
 * @package Saki\Validation
 */
class PhaseValidation extends Validation {
    private $type;

    function __construct(PhaseValidationType $phaseValidationType) {
        $this->type = $phaseValidationType;
    }

    //region Validation impl
    function validate(Round $round, Area $actorArea = null) {
        $phase = $round->getPhase();
        $type = $this->type;
        if (!$type->valid($phase)) {
            throw new ValidateException(
                "Failed asserting phase matches, expected[{$type->getValidPhases()}] but actual[$phase]."
            );
        }
    }
    //endregion
}

/**
 * @package Saki\Validation
 */
class PhaseValidationType extends Enum {
    const ANY = 0;
    const PRIVATE = 1;
    const PUBLIC = 2;
    const PRIVATE_OR_PUBLIC = 3;
    const OVER = 4;

    /**
     * @return Phase[]
     */
    function getValidPhases() {
        $map = [
            self::ANY => [Phase::createAll()],
            self::PRIVATE => [Phase::createPrivate()],
            self::PUBLIC => [Phase::createPublic()],
            self::PRIVATE_OR_PUBLIC => [Phase::createPrivate(), Phase::createPublic()],
            self::ANY => [Phase::createAll()],
        ];
        return $map[$this->getValue()];
    }

    /**
     * @param Phase $phase
     * @return bool
     */
    function valid(Phase $phase) {
        return in_array($phase, $this->getValidPhases());
    }
}