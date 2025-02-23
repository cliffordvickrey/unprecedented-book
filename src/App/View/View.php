<?php

declare(strict_types=1);

namespace CliffordVickrey\Book2024\App\View;

use CliffordVickrey\Book2024\App\DataGrid\DataGrid;
use CliffordVickrey\Book2024\App\Http\Response;
use CliffordVickrey\Book2024\Common\Cache\CacheInterface;
use CliffordVickrey\Book2024\Common\Utilities\CastingUtilities;
use CliffordVickrey\Book2024\Common\Utilities\JsonUtilities;
use Webmozart\Assert\Assert;

class View
{
    private const string NUMBER_FORMATTER_CURRENCY = 'numberFormatterCurrency';
    private const string NUMBER_FORMATTER_NUMBER = 'numberFormatterNumber';
    private const string NUMBER_FORMATTER_PERCENT = 'numberFormatterPercent';
    private const string WEBPACK_CACHE_GROUP_KEY = 'defaultVendors';

    /** @var array<string, list<string>> */
    private static array $sharedVendors = [
        'graph' => ['graph-map'],
        'map' => ['graph-map', 'map'],
    ];

    private ?AssetUris $assetUris = null;
    /** @var list<string> */
    private array $enqueuedScripts = [];
    private ?\IntlDateFormatter $intlDateFormatter = null;
    /** @var array<self::NUMBER_FORMATTER_*, \NumberFormatter> */
    private array $numberFormatters = [];

    public function __construct(private readonly ?CacheInterface $cache = null)
    {
    }

    public function emitCss(string $name): string
    {
        return implode(\PHP_EOL, array_map($this->emitLinkTag(...), $this->getAssetUris("$name.css")));
    }

    /**
     * @return list<string>
     */
    private function getAssetUris(string $filename): array
    {
        if (null === $this->assetUris) {
            $this->assetUris = $this->buildAssetUris();
        }

        return $this->assetUris[$filename]
            ?? throw new \UnexpectedValueException(\sprintf('Could not resolve %s to a filename', $filename));
    }

    private function buildAssetUris(): AssetUris
    {
        $assetUris = $this->cache?->get(AssetUris::class);

        if ($assetUris) {
            return $assetUris;
        }

        $assetUris = AssetUris::build();
        $this->cache?->set($assetUris);

        return $assetUris;
    }

    public function jsonEncode(mixed $value): string
    {
        return JsonUtilities::jsonEncode($value);
    }

    public function enqueueJs(string $name): void
    {
        if (!str_starts_with($name, self::WEBPACK_CACHE_GROUP_KEY)) {
            $vendorBundles = self::$sharedVendors[$name] ?? [$name];

            array_walk(
                $vendorBundles,
                fn (string $vendorBundle) => $this->enqueueJs(self::WEBPACK_CACHE_GROUP_KEY."-$vendorBundle")
            );
        }

        if (!\in_array($name, $this->enqueuedScripts)) {
            $this->enqueuedScripts[] = $name;
        }
    }

    public function emitEnqueuedScripts(): string
    {
        return implode(\PHP_EOL, array_map($this->emitJs(...), $this->enqueuedScripts));
    }

    public function resetState(): void
    {
        $this->enqueuedScripts = [];
    }

    public function emitJs(string $name): string
    {
        return implode(\PHP_EOL, array_map($this->emitScriptTag(...), $this->getAssetUris("$name.js")));
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

    public function formatNumber(mixed $value): string
    {
        return $this->doFormat($value, self::NUMBER_FORMATTER_NUMBER);
    }

    public function formatPercent(mixed $value): string
    {
        return $this->doFormat($value, self::NUMBER_FORMATTER_PERCENT);
    }

    private function emitLinkTag(string $filename): string
    {
        /** @noinspection HtmlUnknownTarget */
        return \sprintf('<link href="%s" rel="stylesheet">', $this->htmlEncode($filename));
    }

    public function htmlEncode(mixed $value): string
    {
        $value = (string) CastingUtilities::toString($value);

        return htmlentities($value, \ENT_QUOTES);
    }

    private function emitScriptTag(string $filename): string
    {
        /** @noinspection HtmlUnknownTarget */
        return \sprintf('<script src="%s"></script>', $this->htmlEncode($filename));
    }
}
