<?php

namespace Plum\SeleniumIdeManager\Http\Controllers;

use Illuminate\Routing\Controller;

class BaseController extends Controller
{
    /**
     * @var array|\Illuminate\Http\Request|string
     */
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }
}
