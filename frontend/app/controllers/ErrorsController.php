<?php

namespace App\Controllers;

use Core\Controller;

class ErrorsController extends Controller
{
    public function show404()
    {
        $this->view('errors/404', [], 'main');
    }
}
