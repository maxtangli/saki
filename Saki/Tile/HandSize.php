<?php

namespace Saki\Tile;

class HandSize {
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

    /**
     * kong hand(public) declare-kong total(public)
     * 0 14(13) 0 14(13)
     * 1 11(10) 4 15(14)
     * 2 8(7) 8 16(15)
     * 3 5(4) 12 17(16)
     * 4 2(1) 16 18(17)
     * @return bool
     */
    function isCompletePrivate() {
        return $this->getCount() >= 14; // todo not right?
    }

    function isPublic() {
        return $this->getCount() && $this->getCount() % 3 == 1;
    }

    function isCompletePublic() {
        return $this->getCount() == 13; // todo not right?
    }

    function isPrivateOrPublic() {
        return $this->isPrivate() || $this->isPublic();
    }

    function isCompletePrivateOrPublic() {
        return $this->isCompletePrivate() || $this->isCompletePublic();
    }

    function equalsPhase(HandSize $other) {
        return ($this->isPrivate() && $other->isPrivate())
        || ($this->isPublic() && $other->isPublic());
    }
}