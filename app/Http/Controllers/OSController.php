<?php


namespace App\Http\Controllers;


use App\Utils\OS\OS;
use Illuminate\Http\Request;

class OSController
{
    public function osSearch(Request $request)
    {
        $data = $request->all();

        OS::easySearch();
    }
}
