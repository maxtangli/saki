<?php
namespace Nodoka\Server;

use Ratchet\ConnectionInterface;

/**
 * @package Nodoka\Server
 */
class NullClient implements ConnectionInterface {
    private $lastReceived = [];
    public $resourceId = 'null-client';

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

    /**
     * Send data to the connection
     * @param  string $data
     * @return \Ratchet\ConnectionInterface
     */
    function send($data) {
        $this->setLastReceived($data);
    }

    /**
     * Close the connection
     */
    function close() {
        // do nothing
    }
}