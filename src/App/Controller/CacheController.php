<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\Controller;

use CliffordVickrey\Book2024\App\Http\Request;
use CliffordVickrey\Book2024\App\Http\Response;
use CliffordVickrey\Book2024\Common\Cache\Cache;
use CliffordVickrey\Book2024\Common\Cache\CacheInterface;
use CliffordVickrey\Book2024\Common\Utilities\StringUtilities;

class CacheController implements ControllerInterface
{
    public const string CACHE_CLEARED = 'cacheCleared';
    public const string MEMORY_FREE = 'memoryFree';
    public const string MEMORY_TOTAL = 'memoryTotal';

    private CacheInterface $cache;

    public function __construct(?CacheInterface $cache = null)
    {
        $this->cache = $cache ?? new Cache();
    }

    public function dispatch(Request $request): Response
    {
        $response = new Response();

        $isPost = $request->isPost();

        if ($isPost) {
            $this->cache->clear();
        }

        $stat = $this->cache->stat();

        $response[self::CACHE_CLEARED] = $isPost;
        $response[self::MEMORY_FREE] = self::toMb($stat['free']);
        $response[self::MEMORY_TOTAL] = self::toMb($stat['total']);

        return $response;
    }

    private static function toMb(int $bytes): string
    {
        $mb = $bytes / (1024 * 1024);

        return \sprintf('%sMB', StringUtilities::numberFormat($mb, 2));
    }
}
