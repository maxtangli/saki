<?php
namespace Saki\Util;

/**
 * @package Saki\Util
 */
abstract class Enum implements Immutable {
    private static $instances;

    /**
     * @param int $value
     * @return bool
     */
    static function validValue(int $value) {
        return isset(static::getValue2StringMap()[$value]);
    }

    /**
     * @param int $value
     * @return static
     */
    static function create(int $value) {
        $class = get_called_class();
        if (!isset(self::$instances[$class][$value])) {
            self::$instances[$class][$value] = new $class($value);
        }
        return self::$instances[$class][$value];
    }

    /**
     * @param string $s
     * @return static
     */
    static function fromString(string $s) {
        $v = array_search($s, static::getValue2StringMap());
        if ($v === false) {
            throw new \InvalidArgumentException("Invalid argument \$s[$s].");
        }
        return static::create($v);
    }

    /**
     * @return array A map of values [enumValue => string].
     */
    static function getValue2StringMap() {
        $result = [];
        $refClass = new \ReflectionClass(get_called_class());
        foreach ($refClass->getConstants() as $name => $value) {
            $text = strtolower(str_replace('_', ' ', $name));
            $result[$value] = $text;
        }
        return $result;
    }

    private $value;

    /**
     * @param int $value
     */
    protected function __construct(int $value) {
        if (!static::validValue($value)) {
            throw new \InvalidArgumentException();
        }
        $this->value = $value;
    }

    private function __clone() {
    }

    // __wakeup() is required to support object reconstruction from $_SESSION.
//    private function __wakeup() {
//    }

    /**
     * @return string
     */
    function __toString() {
        return static::getValue2StringMap()[$this->getValue()];
    }

    /**
     * @return int
     */
    function getValue() {
        return $this->value;
    }

    /**
     * @param array $targetValues
     * @return bool
     */
    protected function isTargetValue(array $targetValues) {
        return in_array($this->getValue(), $targetValues);
    }
}