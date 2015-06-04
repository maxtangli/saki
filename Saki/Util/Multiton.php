<?php
namespace Saki\Util;

class Multiton {
    /**
     * @param int $value
     * @return Multiton
     */
    static function getInstance($value) {
        static $instances = [];
        if (!isset($instances[$value])) {
            $instances[$value] = new static($value);
        }
        return $instances[$value];
    }

    private $value;

    function getValue() {
        return $this->value;
    }

    protected function __construct($value) {
        $this->value = $value;
    }

    private function __clone() {
    }

    private function __wakeup() {
    }
}