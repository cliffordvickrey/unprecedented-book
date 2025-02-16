<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\Controller;

use CliffordVickrey\Book2024\App\DTO\DonorProfileQuery;
use CliffordVickrey\Book2024\App\Http\ContentType;
use CliffordVickrey\Book2024\App\Http\Request;
use CliffordVickrey\Book2024\App\Http\Response;
use CliffordVickrey\Book2024\Common\Enum\State;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;
use CliffordVickrey\Book2024\Common\Utilities\FileUtilities;
use Webmozart\Assert\Assert;

class GeoJsonController extends AbstractController
{
    public function dispatch(Request $request): Response
    {
        $state = self::getState($request);

        return self::getResponse($state);
    }

    private static function getState(Request $request): State
    {
        $stateParam = $request->getQueryParam(DonorProfileQuery::PARAM_STATE);
        $state = CastingUtilities::toString($stateParam) ?? State::USA->value;

        return State::tryFrom($state) ?? State::USA;
    }

    private static function getResponse(State $state): Response
    {
        $response = new Response();
        $response[Response::ATTR_CACHEABLE] = true;
        $response->setObject(ContentType::json);
        $response->setResource(self::getResource($state));

        return $response;
    }

    /**
     * @return resource
     */
    private static function getResource(State $state)
    {
        $ptr = fopen(self::getGeoJsonFilename($state), 'r');
        Assert::notFalse($ptr);

        return $ptr;
    }

    private static function getGeoJsonFilename(State $state): string
    {
        $filename = \sprintf('%s/../../../web-data/geojson/%s.json', __DIR__, strtolower($state->value));

        return FileUtilities::getAbsoluteCanonicalPath($filename);
    }
}
