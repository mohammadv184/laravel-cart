<?php

namespace Mohammadv184\Cart\Tests\helpers;

class ModelFake
{
    /**
     * @var int
     */
    public $id = 1;

    /**
     * @param $key
     *
     * @return $this
     */
    public function find($key)
    {
        $this->id = $key;

        return $this;
    }
}
