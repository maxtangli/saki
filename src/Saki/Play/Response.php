<?php

namespace Saki\Play;

/**
 * @package Saki\Play
 */
class Response {
    /**
     * @param array $json
     * @return Response
     */
    static function createOk(array $json = null) {
        if (!isset($json)) {
            return new self(['response' => 'ok']);
        }

        $isOk = isset($json['response']) && ($json['response'] == 'ok');
        if (!$isOk) {
            throw new \InvalidArgumentException();
        }
        return new self($json);
    }

    /**
     * @param string $string
     * @return Response
     */
    static function createError(string $string) {
        return new self(['response' => 'error', 'message' => $string]);
    }

    private $jsonInArray;

    /**
     * @param array $jsonInArray
     */
    private function __construct(array $jsonInArray) {
        $this->jsonInArray = $jsonInArray;
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

    function getBinary() {
        throw new \InvalidArgumentException('todo');
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