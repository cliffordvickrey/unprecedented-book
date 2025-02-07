<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\View;

use CliffordVickrey\Book2024\App\DataGrid\DataGrid;
use CliffordVickrey\Book2024\App\Http\Response;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;
use Webmozart\Assert\Assert;

class View
{
    private ?\IntlDateFormatter $intlDateFormatter = null;

    public function htmlEncode(mixed $value): string
    {
        $value = (string) CastingUtilities::toString($value);

        return htmlentities($value, \ENT_QUOTES);
    }

    /**
     * @param array<array-key, string>|array<string, array<array-key, string>> $options
     */
    public function select(
        string $id,
        string $name,
        string $label,
        array $options,
        int|string|null $value = null,
        bool $hasBlank = true,
    ): string {
        $partialOptions = [
            'hasBlank' => $hasBlank,
            'id' => $id,
            'name' => $name,
            'label' => $label,
            'options' => $options,
        ];

        if (null !== $value) {
            $partialOptions['value'] = $value;
        }

        return $this->partial('select', $partialOptions);
    }

    /**
     * @param array<string, mixed>|Response $params
     */
    public function partial(string $partial, array|Response $params): string
    {
        $filename = __DIR__."/../../../app/partials/$partial.php";

        Assert::file($filename);

        if (\is_array($params)) {
            $response = new Response();

            foreach ($params as $k => $v) {
                $response[$k] = $v;
            }
        } else {
            $response = clone $params;
        }

        try {
            ob_start();
            require $filename;

            return (string) ob_get_contents();
        } finally {
            ob_end_clean();
        }
    }

    public function formatDate(\DateTimeImmutable $dateTime): string
    {
        if (null === $this->intlDateFormatter) {
            $this->intlDateFormatter = new \IntlDateFormatter(
                'en_US',
                \IntlDateFormatter::SHORT,
                \IntlDateFormatter::NONE
            );
        }

        return (string) $this->intlDateFormatter->format($dateTime);
    }

    public function panel(string $id, string $label, string $content): string
    {
        return $this->partial('panel', ['id' => $id, 'label' => $label, 'content' => $content]);
    }

    public function dataGrid(DataGrid $grid): string
    {
        return $this->partial('grid', [DataGrid::class => $grid]);
    }
}
