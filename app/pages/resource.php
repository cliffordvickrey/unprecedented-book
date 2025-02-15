<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\App\Http\Response;
use Webmozart\Assert\Assert;

$response = $response ?? new Response();
Assert::isInstanceOf($response, Response::class);

$resource = $response->getResource();
fpassthru($resource);
fclose($resource);
