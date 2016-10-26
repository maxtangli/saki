<?php

namespace Saki\Game\Tile;

use Saki\Util\ComparablePriority;
use Saki\Util\Immutable;
use Saki\Util\Utils;

/**
 * compare
 * - ==         :ignore   red
 * - ===        :consider red
 * - toString() :consider red
 * @package Saki\Game\Tile
 */
class Tile implements Immutable {
    use ComparablePriority;

    //region ComparablePriority impl
    function getPriority() {
        return self::$priorities[$this->__toString()];
    }
    //endregion

    function getIgnoreRedPriority() {
        return floor($this->getPriority() / 10) * 10;
    }

    static function getIgnoreRedPrioritySelector() {
        /**
         * @param Tile $v
         * @return int
         */
        return function ($v) {
            return $v->getIgnoreRedPriority();
        };
    }

    const REGEX_SUIT_NUMBER = '[0-9]'; // 0 means red dora 5
    const REGEX_SUIT_TILE = '(' . self::REGEX_SUIT_NUMBER . TileType::REGEX_SUIT_TYPE . ')';
    const REGEX_HONOUR_TILE = TileType::REGEX_HONOUR_TYPE;
    const REGEX_TILE = '(' . self::REGEX_SUIT_TILE . '|' . self::REGEX_HONOUR_TILE . ')';
    private static $priorities = [
        '1m' => 110, '2m' => 120, '3m' => 130, '4m' => 140, '5m' => 150,
        '0m' => 155, '6m' => 160, '7m' => 170, '8m' => 180, '9m' => 190,
        '1p' => 210, '2p' => 220, '3p' => 230, '4p' => 240, '5p' => 250,
        '0p' => 255, '6p' => 260, '7p' => 270, '8p' => 280, '9p' => 290,
        '1s' => 310, '2s' => 320, '3s' => 330, '4s' => 340, '5s' => 350,
        '0s' => 355, '6s' => 360, '7s' => 370, '8s' => 380, '9s' => 390,
        'E' => 410, 'S' => 420, 'W' => 430, 'N' => 440,
        'C' => 510, 'P' => 520, 'F' => 530,
    ];
    private static $instances = [];
    private static $redInstances = [];

    /**
     * @param string $s
     * @return bool
     */
    static function validString(string $s) {
        $regex = '/^' . self::REGEX_TILE . '$/';
        return preg_match($regex, $s) === 1;
    }

    /**
     * @param string $s
     * @return Tile
     */
    static function fromString(string $s) {
        if (!isset(self::$instances[$s])) {
            if (!self::validString($s)) {
                throw new \InvalidArgumentException(
                    sprintf('Invalid $s[%s] for Tile.', $s)
                );
            }

            $tileType = TileType::fromString(substr($s, -1));
            $isRedDora = ($s[0] === '0');
            $number = $isRedDora ? 5 : (strlen($s) == 2 ? intval($s[0]) : null);

            $tile = new Tile($tileType, $number, $isRedDora);

            self::$instances[$s] = $tile;
            if ($isRedDora) {
                self::$redInstances[$s] = $tile;
            }
        }
        return self::$instances[$s];
    }

    private $tileType;
    private $number;

    /**
     * Note: be private to support singleton.
     * @param TileType $tileType
     * @param $number
     * @param $isRedDora
     */
    private function __construct(TileType $tileType, $number, $isRedDora) {
        // already validated by create()
        $this->tileType = $tileType;
        $this->number = $number;
        // $isRedDora already handled by fromString()
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->toFormatString(true);
    }

    /**
     * @param bool $considerRedDora
     * @return string
     */
    function toFormatString(bool $considerRedDora) {
        return ($considerRedDora && $this->isRedDora() ? '0' : $this->number)
        . $this->tileType;
    }

    /**
     * @return bool
     */
    function ableToRed() {
        return $this->isSuit() && $this->getNumber() == 5;
    }

    /**
     * @return Tile
     */
    function toRed() {
        return self::fromString('0' . $this->tileType); // validate
    }

    /**
     * @return Tile
     */
    function toNotRed() {
        return self::fromString($this->toFormatString(false));
    }

    /**
     * @return TileType
     */
    function getTileType() {
        return $this->tileType;
    }

    /**
     * @return int
     */
    function getNumber() {
        if (!$this->tileType->isSuit()) {
            throw new \BadMethodCallException('getNumber() is not supported on non-suit tile.');
        }
        return $this->number;
    }

    /**
     * @return bool
     */
    function isRedDora() {
        return in_array($this, self::$redInstances, true);
    }

    /**
     * @return bool
     */
    function isSuit() {
        return $this->getTileType()->isSuit();
    }

    /**
     * @return bool
     */
    function isHonour() {
        return $this->getTileType()->isHonour();
    }

    /**
     * @return bool
     */
    function isWind() {
        return $this->getTileType()->isWind();
    }

    /**
     * @return bool
     */
    function isDragon() {
        return $this->getTileType()->isDragon();
    }

    /**
     * @return bool
     */
    function isSimple() {
        return $this->isSuit() && !in_array($this->getNumber(), [1, 9]);
    }

    /**
     * @return bool
     */
    function isTerm() {
        return $this->isSuit() && in_array($this->getNumber(), [1, 9]);
    }

    /**
     * @return bool
     */
    function isTermOrHonour() {
        return $this->isTerm() || $this->isHonour();
    }

    /**
     * @param int $offset
     * @return Tile
     */
    function getNextTile(int $offset = 1) {
        $currentType = $this->getTileType();

        if ($currentType->isSuit()) {
            $currentNumber = $this->getNumber();
            $nextNumber = Utils::normalizedMod($currentNumber + $offset - 1, 9) + 1;
            $s = $nextNumber . $currentType;
        } else {
            $all = $currentType->isWind() ? 'ESWN' : 'CPF';
            $currentIndex = strpos($all, $currentType->__toString());
            $nextIndex = Utils::normalizedMod($currentIndex + $offset, strlen($all));
            $s = $all[$nextIndex];
        }

        return self::fromString($s);
    }
}