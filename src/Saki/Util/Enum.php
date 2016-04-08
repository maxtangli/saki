<?php
namespace Saki\Util;

abstract class Enum {
    private static $instances;

    static function validValue(int $value) {
        return isset(static::getValue2StringMap()[$value]);
    }

    /**
     * @param int $value
     * @return static
     */
    static function create(int $value) {
        $class = static::getClassName();
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

//    // __wakeup() is required to support object reconstruction from $_SESSION.
//    private function __wakeup() {
//    }

    function __toString() {
        return static::getValue2StringMap()[$this->getValue()];
    }

    static function getValue2StringMap() {
        $r = [];
        $refClass = new \ReflectionClass(get_called_class());
        foreach ($refClass->getConstants() as $name => $value) {
            $text = strtolower(str_replace('_', ' ', $name));
            $r[$value] = $text;
        }
        return $r;
    }

    protected function isTargetValue(array $targetValues) {
        return in_array($this->getValue(), $targetValues);
    }
}