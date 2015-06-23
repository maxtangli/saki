<?php
namespace Saki\Game;

use Saki\Util\Enum;

class RoundPhase extends Enum {
    const INIT_PHASE = 1;
    const PRIVATE_PHASE = 2;
    const PUBLIC_PHASE = 3;
    const OVER_PHASE = 4;

    static function getValue2StringMap() {
        return [
            self::INIT_PHASE => 'init phase',
            self::PRIVATE_PHASE => 'private phase',
            self::PUBLIC_PHASE => 'public phase',
            self::OVER_PHASE => 'over phase',
        ];
    }

    /**
     * @param $value
     * @return RoundPhase
     */
    static function getInstance($value) {
        return parent::getInstance($value);
    }

    /**
     * @param string $s
     * @return RoundPhase
     */
    static function fromString($s) {
        return parent::fromString($s);
    }


}