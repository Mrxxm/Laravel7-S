<?php


namespace App\Utils\OS;


use Illuminate\Support\Facades\Facade;

class OS extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'App\Utils\OS\OpenSearch';
    }
}
