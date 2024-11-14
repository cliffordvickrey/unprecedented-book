#!/usr/bin/php
<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\Common\Http\Downloader;
use GuzzleHttp\Psr7\Stream;

require_once __DIR__.'/../../vendor/autoload.php';

call_user_func(function () {
    $chunkSize = 1024 * 1024;

    $downloader = new Downloader();
    $str = $downloader->download('https://www.fec.gov/files/')->getContents();

    preg_match_all('/<Key>([^<]+)<\/Key>/i', $str, $matches);

    $filenames = array_filter(
        $matches[1],
        fn ($match) => (bool) preg_match('/\/20(08|10|12|14|16|18|20|22|24)\//', $match)
    );

    $urls = array_map(fn ($filename) => 'https://www.fec.gov/files/'.$filename, $filenames);

    foreach ($urls as $url) {
        echo "Downloading from $url ... ";

        $destination = __DIR__.'/../../data/bulk-data/'.basename($url);

        if (is_file($destination)) {
            echo 'already saved'.\PHP_EOL;
            continue;
        }

        $stream = $downloader->download($url);

        echo 'success!'.\PHP_EOL;

        $resource = fopen($destination, 'w') ?: throw new UnexpectedValueException();
        $output = new Stream($resource);

        while (!$stream->eof()) {
            $buffer = $stream->read($chunkSize);
            $output->write($buffer);
        }

        $stream->close();
        $output->close();
    }
});
