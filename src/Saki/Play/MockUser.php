<?php

namespace Saki\Play;

use Saki\Util\ArrayList;
use Saki\Util\Utils;

/**
 * @package Saki\Play
 */
class MockUser implements UserProxy {
    private $id;
    private $receiveList;

    function __construct() {
        $this->id = Utils::generateRandomToken('mockUser');
        $this->receiveList = new ArrayList();
    }

    /**
     * @return ArrayList
     */
    function getReceiveList() {
        return $this->receiveList;
    }

    /**
     * @param array $json
     */
    function send(array $json) {
        $this->receiveList->insertLast(json_encode($json));
    }

    //region UserProxy impl
    function getId() {
        return $this->id;
    }

    function sendRound(array $json) {
        $this->send($json);
    }

    function sendOk() {
        $this->send([
            'response' => 'ok'
        ]);
    }

    function sendError(string $message) {
        $this->send([
            'response' => 'error',
            'message' => $message
        ]);
    }
    //endregion
}