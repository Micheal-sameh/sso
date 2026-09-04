<?php

namespace App\Http\Controllers;

use App\Http\Traits\HandlesInertiaResponses;
use Illuminate\Http\Request;
use Inertia\Response;

class HomeController extends BaseController
{
    use HandlesInertiaResponses;

    public function index(Request $request): Response
    {
        return $this->inertia('Home/Index');
    }
}
