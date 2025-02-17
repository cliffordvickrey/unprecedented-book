<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\Controller;

use CliffordVickrey\Book2024\App\DTO\DonorProfileQuery;
use CliffordVickrey\Book2024\App\Http\Request;
use CliffordVickrey\Book2024\App\Http\Response;

abstract class AbstractD3Controller extends AbstractController
{
    public function dispatch(Request $request): Response
    {
        $response = new Response();

        $query = DonorProfileQuery::fromRequest($request);
        $response->setObject($query);

        if (null === $query->campaignType) {
            $response[Response::ATTR_PARTIAL] = 'frequencies';
        }

        return $response;
    }
}
