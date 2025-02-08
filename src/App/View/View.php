<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\View;

use CliffordVickrey\Book2024\App\DataGrid\DataGrid;
use CliffordVickrey\Book2024\App\Http\Response;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;
use Webmozart\Assert\Assert;

class View
{
    private const string NUMBER_FORMATTER_CURRENCY = 'numberFormatterCurrency';
    private const string NUMBER_FORMATTER_NUMBER = 'numberFormatterNumber';
    private const string NUMBER_FORMATTER_PERCENT = 'numberFormatterPercent';

    /** @var array<self::NUMBER_FORMATTER_*, \NumberFormatter> */
    private array $numberFormatters = [];
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

        $view = $this;

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

    public function formatCurrency(mixed $value): string
    {
        return $this->doFormat($value, self::NUMBER_FORMATTER_CURRENCY);
    }

    public function formatNumber(mixed $value): string
    {
        return $this->doFormat($value, self::NUMBER_FORMATTER_NUMBER);
    }

    public function formatPercent(mixed $value): string
    {
        return $this->doFormat($value, self::NUMBER_FORMATTER_PERCENT);
    }

    /**
     * @param self::NUMBER_FORMATTER_* $type
     */
    private function doFormat(mixed $value, string $type): string
    {
        $value = CastingUtilities::toNumeric($value);

        if (null === $value) {
            return '';
        }

        return (string) $this->getNumberFormatter($type)->format($value);
    }

    /**
     * @phpstan-param self::NUMBER_FORMATTER_* $type
     */
    private function getNumberFormatter(string $type): \NumberFormatter
    {
        $this->numberFormatters[$type] ??= $this->resolveNumberFormatter($type);

        return $this->numberFormatters[$type];
    }

    /**
     * @phpstan-param self::NUMBER_FORMATTER_* $type
     */
    private function resolveNumberFormatter(string $type): \NumberFormatter
    {
        switch ($type) {
            case self::NUMBER_FORMATTER_CURRENCY:
                return new \NumberFormatter('en_US', \NumberFormatter::CURRENCY);
            case self::NUMBER_FORMATTER_PERCENT:
                $numberFormatter = new \NumberFormatter('en_US', \NumberFormatter::PERCENT);
                $numberFormatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 2);

                return $numberFormatter;
            default:
                return new \NumberFormatter('en_US', \NumberFormatter::DECIMAL);
        }
    }
}
