<?php
namespace Saki\Util;

interface IEnum {
    static function getValue2StringMap();
}

abstract class Enum implements IEnum {
    private static $instances;

    static function validValue($value) {
        return isset(static::getValue2StringMap()[$value]);
    }

    static function validString($s) {
        return array_search($s, static::getValue2StringMap()) !== false;
    }

    /**
     * @param $value
     * @return object
     */
    static function getInstance($value) {
        $class = static::getClassName();
        if (!isset(self::$instances[$class][$value])) {
            self::$instances[$class][$value] = new $class($value);
        }
        return self::$instances[$class][$value];
    }

    /**
     * @param string $s
     * @return object
     */
    static function fromString($s) {
        $v = array_search($s, static::getValue2StringMap());
        if ($v === false) {
            throw new \InvalidArgumentException("Invalid argument \$s[$s].");
        }
        return static::getInstance($v);
    }

    private static function getClassName() {
        return get_called_class();
    }

    private $value;

    function getValue() {
        return $this->value;
    }

    protected function __construct($value) {
        if (!static::validValue($value)) {
            throw new \InvalidArgumentException();
        }
        $this->value = $value;
    }

    private function __clone() {
    }

    private function __wakeup() {
    }

    function __toString() {
        return static::getValue2StringMap()[$this->getValue()];
    }
}