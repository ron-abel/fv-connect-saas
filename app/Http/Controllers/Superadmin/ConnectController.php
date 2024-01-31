<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Response;


class ConnectController extends Controller
{
	public function index()
	{
        return $this->_loadContent('superadmin.pages.index', []);
	}
}
