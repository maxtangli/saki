<?php

namespace Nodoka\Server;

use Ratchet\ConnectionInterface;
use Saki\Util\Utils;

/**
 * @package Nodoka\Server
 */
class MockClient implements ConnectionInterface {
    public $resourceId;
    /** @var string[] */
    private $receivedHistory;

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
        $this->receivedHistory = [];
        $this->resourceId = Utils::generateRandomToken('Mock');
    }

    /**
     * @return string[]
     */
    function getReceivedHistory() {
        return $this->receivedHistory;
    }

    /**
     * @param int $index
     * @param bool $decode
     * @return mixed|string
     */
    function getReceived($index = -1, $decode = true) {
        $actualIndex = $index >= 0 ? $index : count($this->receivedHistory) + $index;
        if (!isset($this->receivedHistory[$actualIndex])) {
            throw new \InvalidArgumentException(
                sprintf("Invalid actualIndex[$actualIndex] of index[$index] for receivedHistory[%s].",
                    implode("\n", $this->receivedHistory))
            );
        }

        $data = $this->receivedHistory[$actualIndex];
        return $decode ? json_decode($data) : $data;
    }

    /**
     * @param string $received
     */
    function pushReceived(string $received) {
        $this->receivedHistory[] = $received;
    }

    function clearReceived() {
        $this->receivedHistory = [];
    }

    //region ConnectionInterface impl

    /**
     * Send data to the connection
     * @param  string $data
     * @return \Ratchet\ConnectionInterface
     */
    function send($data) {
        $this->pushReceived($data);
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