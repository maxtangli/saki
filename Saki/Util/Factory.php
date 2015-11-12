<?php

namespace Saki\Util;

class Factory {
    private static $factoryOfFactories;

    static function getInstance($factoryKey) {
        self::$factoryOfFactories = self::$factoryOfFactories ?: new Factory();
        return self::$factoryOfFactories->getOrGenerate($factoryKey, function () {
            return new Factory();
        });
    }

    protected $instances = [];

    function getOrGenerate($key, callable $generator) {
        if (!$this->exist($key)) {
            $this->add($key, $generator());
        }
        return $this->get($key);
    }

    function exist($key) {
        return isset($this->instances[$key]);
    }

    function get($key) {
        if (!$this->exist($key)) {
            throw new \InvalidArgumentException();
        }
        return $this->instances[$key];
    }

    function add($key, $value) {
        if ($this->exist($key)) {
            throw new \InvalidArgumentException();
        }
        $this->instances[$key] = $value;
    }
}