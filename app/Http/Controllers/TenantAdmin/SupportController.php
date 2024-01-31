<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;


class SupportController extends Controller
{
    public function __construct()
    {
        Controller::setSubDomainName();
    }

    /**
     * [GET] Support Page for Admin
     */
    public function index()
    {
        return $this->_loadContent('admin.pages.support');
    }
}
