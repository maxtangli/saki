<?php

namespace Saki\Play;

use Nodoka\Server\AIClient;

/**
 * @package Saki\Play
 */
class Participant {
    private $userKey;
    private $role;
    private $roundSerializer;

    /**
     * @param $userKey
     * @param Role $role
     * @param RoundSerializer $roundSerializer
     */
    function __construct($userKey, Role $role, RoundSerializer $roundSerializer) {
        $this->userKey = $userKey;
        $this->role = $role;
        $this->roundSerializer = $roundSerializer;
    }

    /**
     * @return string
     */
    function __toString() {
        $userKey = $this->getUserKey();

        if (isset($userKey->resourceId)) {
            $id = $userKey->resourceId; // todo remove
        } else {
            $id = $userKey->getId(); // todo remove
        }

        return $id . ',' . $this->getRole();
    }

    /**
     * @return mixed
     */
    function getUserKey() {
        return $this->userKey;
    }

    /**
     * @return Role
     */
    function getRole() {
        return $this->role;
    }

    /**
     * @return RoundSerializer
     */
    function getRoundSerializer() {
        return $this->roundSerializer;
    }

    /**
     * @return bool
     */
    function isAI() {
        // todo remove client logic
        return $this->getUserKey() instanceof AIClient;
    }
}