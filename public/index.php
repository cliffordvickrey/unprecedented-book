<?php

/** @noinspection PhpUnusedParameterInspection */

declare(strict_types=1);

use CliffordVickrey\Book2024\App\Error\ErrorHandler;
use CliffordVickrey\Book2024\App\Http\ContentType;
use CliffordVickrey\Book2024\App\Http\Request;
use CliffordVickrey\Book2024\App\Http\Response;
use CliffordVickrey\Book2024\App\Http\Route;
use CliffordVickrey\Book2024\App\View\View;
use Webmozart\Assert\Assert;

call_user_func(function () {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');

    chdir(__DIR__);

    require_once __DIR__.'/../vendor/autoload.php';

    set_error_handler(ErrorHandler::handleError(...));
    $view = new View();

    try {
        $request = Request::fromSuperGlobals();
        $route = Route::fromRequest($request);

        $controllerClassStr = $route->getControllerClassStr();
        $controller = new $controllerClassStr();
        $response = $controller->dispatch($request);
        $response->setObject($route);
    } catch (Throwable $ex) {
        error_log((string) $ex);

        $view->resetState();

        $response = new Response();
        $response->setObject(Route::error);

        http_response_code(500);
    }

    $content = null;

    $contentType = $response->getObjectNullable(ContentType::class) ?? ContentType::html;
    $isHtml = ContentType::html === $contentType;

    if ($response->getResourceNullable()) {
        $response[Response::ATTR_PARTIAL] = 'resource';
    } elseif (ContentType::json === $contentType) {
        $response[Response::ATTR_PARTIAL] = 'json';
    }

    header('Content-Type: '.$contentType->toHeaderValue());

    if ($response->getAttributeNullable(Response::ATTR_CACHEABLE, false)) {
        $cacheHeaders = ['Cache-Control', 'Expires', 'Pragma'];
        array_walk($cacheHeaders, static fn ($header) => header_remove($header));

        header('Cache-Control: max-age=31536000, public');
        header('Pragma: cache');
        header('Expires: '.gmdate('D, d M Y H:i:s', time() + 31536000).' GMT');
    }

    if ($isHtml) {
        ob_start();
    }

    try {
        $page = $response->getAttributeNullable(Response::ATTR_PARTIAL, '') ?? $response
            ->getObject(Route::class)
            ->value;

        $file = realpath(__DIR__."/../app/pages/$page.php");

        Assert::notFalse($file);

        call_user_func(function (string $__file, Response $response, View $view) {
            include $__file;
        }, $file, $response, $view);
    } finally {
        if ($isHtml) {
            $content = (string) ob_get_contents();
            ob_end_clean();
        }
    }

    if (null !== $content) {
        echo $view->partial('layout', [Response::ATTR_CONTENT => $content]);
    }
});
