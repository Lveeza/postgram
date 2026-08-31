<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function index()
    {
        return [
            'status' => 'ok',
            'db_connected' => true,
        ];
    }
}
