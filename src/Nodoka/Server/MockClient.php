<?php

namespace Nodoka\Server;

use Ratchet\ConnectionInterface;

/**
 * @package Nodoka\Server
 */
class MockClient implements ConnectionInterface {
    /**
     * @return int
     */
    private static function generateResourceId() {
        static $nextId = 1;
        return 'AI-' . $nextId++;
    }

    private $lastReceived;
    public $resourceId;

    function __construct() {
        $this->clear();
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->resourceId;
    }

    function clear() {
        $this->lastReceived = '';
        $this->resourceId = static::generateResourceId();
    }

    /**
     * @return array
     */
    function getLastReceived() {
        return json_decode($this->lastReceived);
    }

    /**
     * @param null $lastReceived
     */
    function setLastReceived($lastReceived) {
        $this->lastReceived = $lastReceived;
    }

    //region ConnectionInterface impl

    /**
     * Send data to the connection
     * @param  string $data
     * @return \Ratchet\ConnectionInterface
     */
    function send($data) {
        $this->setLastReceived($data);
        return $this;
    }

    /**
     * Close the connection
     */
    function close() {
        $this->clear();
    }
    //endregion
}