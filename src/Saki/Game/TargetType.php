<?php
namespace Saki\Game;

use Saki\Util\Enum;

/**
 * [x] In public-wait, target tile is last-open-tile(DISCARD or KONG).
 *     => still fetch-able in next private, like extendKong does
 *     => generate KEEP-self in private, claim, like extendKong does
 *     => benefit? no Hand change
 *
 * In private-enter, handle draw or claim
 * In private-wait, target tile already set, Hand is always private-style
 *
 * TargetType transfer
 * draw          NULL => private-enter => DRAW-self => private-wait
 * concealedKong ANY-self => private-wait => claim => NULL => REPLACE-self => private-wait
 * extendKong    ANY-self => private-wait => claim => NULL => [KONG-other] => public-wait
 *               => passAll => NULL
 *               => private-enter => KEEP-self => claim => NULL => REPLACE-self => private-wait
 * discard       ANY-self => private-wait => claim => NULL => [DISCARD-other] => public-wait
 *
 * chow, pung    [ANY-other] => public-wait => claim => NULL => KEEP-self => private-wait
 * kong          [ANY-other] => public-wait => claim => NULL => REPLACE-self => private-wait
 * passPublic    [ANY-other] => public-wait => NULL => private-wait
 * @package Saki\Game
 */
class TargetType extends Enum {
    const DRAW = 0;
    const REPLACE = 1;
    const KEEP = 2;
    const DISCARD = 3;
    const KONG = 4;

    /**
     * @return bool
     */
    function isOwnByCreator() {
        switch ($this->getValue()) {
            case self::DRAW:
            case self::REPLACE:
            case self::KEEP:
                return true;
            case self::DISCARD:
            case self::KONG:
                return false;
            default:
                throw new \LogicException();
        }
    }
}