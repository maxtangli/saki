<?php

use Saki\Game\PrevailingStatus;
use Saki\Game\Round;

class SakiTestCase extends \PHPUnit_Framework_TestCase {
    //region override
    static function assertEquals($expected, $actual, $message = '', $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false) {
        if ($message === '' && is_object($expected) && is_object($actual)) {
            $message = sprintf(
                'Failed asserting that two objects are equal, $expected[%s] but $actual[%s].',
                $expected, $actual
            );
        }
        parent::assertEquals($expected, $actual, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
    }

    //endregion

    private static $r;

    /**
     * @param PrevailingStatus|null $rebugResetData
     * @return Round
     */
    static function getInitRound(PrevailingStatus $rebugResetData = null) {
        self::$r = self::$r ?? new Round();
        self::$r->debugInit($rebugResetData ?? PrevailingStatus::createFirst());
        return self::$r;
    }

    /**
     * @return Round
     */
    static function getCurrentRound() {
        return self::$r ?? self::getInitRound();
    }
}
