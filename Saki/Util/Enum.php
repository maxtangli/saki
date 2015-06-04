<?php
namespace Saki\Util;

class Enum extends Multiton{
    static function getValue2StringMap() {
        return [];
    }

    static function validValue($value) {
        return isset(static::getValue2StringMap()[$value]);
    }

    static function validString($s) {
        return array_search($s, static::getValue2StringMap()) !== false;
    }

    /**
     * @param int $value
     * @return Enum
     */
    static function getInstance($value) {
        return parent::getInstance($value);
    }

    /**
     * @param string $s
     * @return Enum
     */
    static function fromString($s) {
        $v = array_search($s, static::getValue2StringMap());
        if ($v===false) {
            throw new \InvalidArgumentException();
        }
        return self::getInstance($v);
    }

    protected function __construct($value) {
        if (!self::validValue($value)) {
            throw new \InvalidArgumentException();
        }
        parent::__construct($value);
    }

    function __toString() {
        return static::getValue2StringMap()[$this->getValue()];
    }
}