<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\Controller;

use CliffordVickrey\Book2024\App\Http\Request;
use CliffordVickrey\Book2024\App\Http\Response;

class ErrorController implements ControllerInterface
{
    public function __construct()
    {
    }

    public function dispatch(Request $request): Response
    {
        return new Response();
    }
}
