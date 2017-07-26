<?php

namespace Yadahan\Cardcom\Facades;

use Illuminate\Support\Facades\Facade;

class Cardcom extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'cardcom';
    }
}
