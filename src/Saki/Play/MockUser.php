<?php

namespace Saki\Play;

use Saki\Util\ArrayList;
use Saki\Util\Utils;

/**
 * @package Saki\Play
 */
class MockUser implements UserProxy {
    private $id;
    private $responseList;

    function __construct() {
        $this->id = Utils::generateRandomToken('mockUser');
        $this->responseList = new ArrayList();
    }

    /**
     * @return ArrayList
     */
    function getResponseList() {
        return $this->responseList;
    }

    //region UserProxy impl
    function getId() {
        return $this->id;
    }

    function send(Response $response) {
        $this->responseList->insertLast($response);
    }
    //endregion
}