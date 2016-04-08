<?php

namespace Saki\Util;

class Pool {
    private static $poolOfPools;

    static function create($factoryKey) {
        self::$poolOfPools = self::$poolOfPools ?? new Pool();
        return self::$poolOfPools->getOrGenerate($factoryKey, function () {
            return new Pool();
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
        return $value;
    }
}