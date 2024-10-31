<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Http;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\StreamInterface;

class Downloader
{
    public function __construct(private ?ClientInterface $client = null)
    {
    }

    /**
     * @throws GuzzleException
     */
    public function download(string $url): StreamInterface
    {
        return $this->getClient()->request('GET', $url)->getBody();
    }

    private function getClient(): ClientInterface
    {
        $this->client ??= self::getDefaultClient();

        return $this->client;
    }

    private static function getDefaultClient(): Client
    {
        $verify = __DIR__.'/cacert.pem';

        if (!is_file($verify)) {
            $verify = true;
        }

        return new Client([RequestOptions::VERIFY => $verify]);
    }
}
