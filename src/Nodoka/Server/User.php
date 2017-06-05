<?php

namespace Nodoka\server;

/**
 * @package Nodoka\server
 */
class User {
    private $username;

    function __construct(string $username) {
        $this->username = $username;
    }

    /**
     * @return string
     */
    function __toString() {
        return "user {$this->getId()}";
    }

    /**
     * @return int|string
     */
    function getId() {
        return $this->username;
    }

    /**
     * @return string
     */
    function getUsername() {
        return $this->username;
    }
}