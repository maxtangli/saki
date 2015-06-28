<?php
namespace Saki\Util;

class ArrayLikeSet extends ArrayLikeObject {
    protected function innerArrayChangedHook() {
        $this->setInnerArray(array_unique($this->toArray()));
    }
}