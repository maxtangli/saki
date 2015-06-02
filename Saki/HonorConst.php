<?php
namespace Saki;

class HonorConst {
    const EAST = 11;
    const SOUTH = 12;
    const WEST = 13;
    const NORTH = 14;
    const RED = 15;
    const GREEN = 16;
    const WHITE = 17;

    static function validValue($v) {
        return 11 <= $v && $v <= 17;
    }
    static function toString($v) {
        return [ // what a mess to mix English and Japanese styles :(
            HonorConst::EAST => '東',
            HonorConst::SOUTH => '南',
            HonorConst::WEST => '西',
            HonorConst::NORTH => '北',
            HonorConst::RED => '中',
            HonorConst::GREEN => '発',
            HonorConst::WHITE => '白',
        ][$v];
    }
}