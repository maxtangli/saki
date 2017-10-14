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

    function __construct(string $id = null) {
        $this->id = $id ?? Utils::generateRandomToken('mockUser');
        $this->responseList = new ArrayList();
    }

    /**
     * @return string
     */
    function __toString() {
        return sprintf('MockUser[%s]', $this->getId());
    }

    /**
     * @return ArrayList
     */
    function getResponseList() {
        return $this->responseList;
    }

    /**
     * @return Response
     */
    function getLastResponse() {
        return $this->getResponseList()->getLast();
    }

    function clearResponseList() {
        $this->responseList->removeAll();
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