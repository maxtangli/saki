<?php
namespace Saki;

class SuitConst {
    const BAMBOO = 101;
    const CHARACTER = 102;
    const DOT = 103;

    static function validValue($v) {
        return 101 <= $v && $v <= 103;
    }
    static function toString($v) {
        return [
            SuitConst::BAMBOO => 's',
            SuitConst::CHARACTER => 'm',
            SuitConst::DOT => 'p',
        ][$v];
    }
}