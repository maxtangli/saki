<?php
namespace Nodoka\Server;

use Ratchet\ConnectionInterface;

/**
 * @package Nodoka\Server
 */
class LocalClient implements ConnectionInterface {
    private $lastReceived;
    public $resourceId;

    function __construct() {
        $this->lastReceived = '';
        $this->resourceId = 'local-' . spl_object_hash($this);
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->resourceId;
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
    }

    /**
     * Close the connection
     */
    function close() {
        // do nothing
    }
    //endregion
}