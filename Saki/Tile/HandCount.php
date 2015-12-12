<?php

namespace Saki\Tile;

class HandCount {
    private $count;

    function __construct($count, $validate = false) {
        $this->count = $count;

        if ($validate && !$this->isValid()) {
            throw new \InvalidArgumentException(
                sprintf('Invalid hand count[%s].', $count)
            );
        }
    }

    function __toString() {
        $countToken = $this->getCount();
        $phaseToken = $this->isPrivate() ? ',private' : ($this->isPublic() ? ',public' : ',invalid');
        $completeToken = $this->isCompletePrivateOrPublic() ? ',complete' : '';
        return $countToken . $phaseToken . $completeToken;
    }

    function getCount() {
        return $this->count;
    }

    function isValid() {
        return $this->isPrivate() || $this->isPublic();
    }

    function isPrivate() {
        return $this->getCount() && $this->getCount() % 3 == 2;
    }

    function isCompletePrivate() {
        return $this->getCount() == 14;
    }

    function isPublic() {
        return $this->getCount() && $this->getCount() % 3 == 1;
    }

    function isCompletePublic() {
        return $this->getCount() == 13;
    }

    function isPrivateOrPublic() {
        return $this->isPrivate() || $this->isPublic();
    }

    function isCompletePrivateOrPublic() {
        return $this->isCompletePrivate() || $this->isCompletePublic();
    }

    function equalsPhase(HandCount $other) {
        return ($this->isPrivate() && $other->isPrivate())
        || ($this->isPublic() && $other->isPublic());
    }
}