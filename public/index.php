<?php

/** @noinspection PhpUnusedParameterInspection */

declare(strict_types=1);

use CliffordVickrey\Book2024\App\Controller\IndexController;
use CliffordVickrey\Book2024\App\Error\ErrorHandler;
use CliffordVickrey\Book2024\App\Http\Request;
use CliffordVickrey\Book2024\App\Http\Response;
use CliffordVickrey\Book2024\App\View\View;
use CliffordVickrey\Book2024\Common\Exception\BookRuntimeException;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);

chdir(__DIR__);

require_once __DIR__.'/../vendor/autoload.php';

set_error_handler(ErrorHandler::handleError(...));
$view = new View();

try {
    $request = Request::fromSuperGlobals();

    $controller = new IndexController();
    $response = $controller->dispatch($request);
} catch (Throwable $ex) {
    error_log((string) $ex);
    $response = new Response();
    $response[Response::ATTR_PAGE] = 'error';
}

$content = null;

$layout = $response->getAttribute(Response::ATTR_LAYOUT, false);

if ($layout) {
    ob_start();
}

try {
    $page = CastingUtilities::toString($response[Response::ATTR_PAGE]);

    if (null === $page) {
        throw new BookRuntimeException('Could not resolve page to go to', 404);
    }

    $file = realpath(__DIR__."/../app/pages/$page.php");

    if (false === $file || !is_file($file)) {
        throw new BookRuntimeException("Invalid page, $page");
    }

    call_user_func(function (string $__file, Response $response, View $view) {
        include $__file;
    }, $file, $response, $view);
} finally {
    if ($layout) {
        $content = (string) ob_get_contents();
        ob_end_clean();
    }
}

if (null !== $content) {
    echo $view->partial('layout', [
        Response::ATTR_CONTENT => $content,
        Response::ATTR_JS => $response->getAttribute(Response::ATTR_JS, false),
    ]);
}
