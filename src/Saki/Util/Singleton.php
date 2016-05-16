<?php
namespace Saki\Util;

/**
 * @see originated from http://www.phptherightway.com/pages/Design-Patterns.html
 * @package Saki\Util
 */
abstract class Singleton {
    private static $instances;

    /**
     * @return static the singleton instance of class
     */
    static function create() {
        $class = get_called_class();
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new $class();
        }
        return self::$instances[$class];
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    protected function __construct() {
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * *Singleton* instance.
     */
    private function __clone() {
    }

//    /**
//     * Private unserialize method to prevent unserializing of the *Singleton*
//     * instance.
//     */
//    private function __wakeup() {
//    }
}