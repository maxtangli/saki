<?php

namespace Saki\Play;

/**
 * @package Saki\Play
 */
class MockJson {
    private $jsonInArray;

    /**
     * @param array $json
     */
    function __construct(array $json) {
        $this->jsonInArray = $json;
    }

    /**
     * @return string
     */
    function getJsonInString() {
        return json_encode($this->jsonInArray);
    }

    /**
     * @return array
     */
    function getJsonInArray() {
        return $this->jsonInArray;
    }

    /**
     * @return bool
     */
    function isRound() {
        $json = $this->getJsonInArray();
        return isset($json['round']);
    }

    /**
     * @return bool
     */
    function isOk() {
        $json = $this->getJsonInArray();
        return isset($json['response']) && ($json['response'] == 'ok');
    }

    /**
     * @param string|null $message
     * @return bool
     */
    function isError(string $message = null) {
        $json = $this->getJsonInArray();
        return isset($json['response'])
            && ($json['response'] == 'error')
            && (!isset($message) || $json['message'] == $message);
    }
}