<?php

namespace Nodoka\server;

/**
 * @package Nodoka\server
 */
class TableUser {
    private $user;
    private $ready;

    /**
     * @param $user
     */
    function __construct(User $user) {
        $this->user = $user;
        $this->ready = false;
    }

    /**
     * @return array
     */
    function toJson() {
        return [
            'username' => $this->getUser()->getUsername(),
            'ready' => $this->isReady()
        ];
    }

    /**
     * @return User
     */
    function getUser() {
        return $this->user;
    }

    /**
     * @return bool
     */
    function isReady() {
        return $this->ready;
    }

    /**
     * @param bool $ready
     */
    function setReady(bool $ready) {
        $this->ready = $ready;
    }
}