<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\Common\Csv;

use CliffordVickrey\Book2024\Common\Exception\BookUnexpectedValueException;
use CliffordVickrey\Book2024\Common\Utilities\FileUtilities;
use Webmozart\Assert\Assert;

abstract class AbstractResource
{
    /** @var resource|null */
    protected mixed $resource = null;

    public function __construct(protected string $filename, protected string $mode = 'r')
    {
    }

    /**
     * @return resource
     */
    public function detach()
    {
        if ($this->isResourceValid($this->resource)) {
            $resource = $this->resource;
            $this->resource = null;

            return $resource;
        }

        throw new BookUnexpectedValueException('Expected valid, open resource');
    }

    /**
     * @phpstan-assert-if-true resource $resource
     */
    protected function isResourceValid(mixed $resource): bool
    {
        return \is_resource($resource) && 'resource (closed)' !== \gettype($resource);
    }

    public function __destruct()
    {
        $this->close();
    }

    public function close(): void
    {
        $resource = $this->resource;

        if ($this->isResourceValid($resource)) {
            fclose($resource);
        }

        $this->resource = null;

        $this->doClose();
    }

    protected function doClose(): void
    {
    }

    /**
     * @return resource
     */
    protected function getResource()
    {
        $resource = $this->resource;

        if ($this->isResourceValid($resource)) {
            return $resource;
        }

        if (str_contains($this->mode, 'w')) {
            FileUtilities::ensureFileDirectory($this->filename);
        }

        $resource = fopen($this->filename, $this->mode);
        Assert::resource($resource, message: \sprintf('Could not open %s', $this->filename));
        $this->resource = $resource;

        return $resource;
    }
}
