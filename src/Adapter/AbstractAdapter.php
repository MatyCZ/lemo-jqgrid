<?php

declare(strict_types=1);

namespace Lemo\JqGrid\Adapter;

use IntlDateFormatter;
use Lemo\JqGrid\AdapterInterface;
use Lemo\JqGrid\Grid;
use Locale;

use function ceil;
use function date;
use function explode;
use function preg_match;
use function str_pad;
use function str_split;
use function strpos;
use function strtolower;
use function trim;

use const STR_PAD_LEFT;

abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * Number of filtered items
     */
    protected int $countItems = 0;

    /**
     * Number of items
     */
    protected int $countItemsTotal = 0;

    /**
     * Is the grid prepared?
     */
    protected bool $isPrepared = false;

    protected ?Grid $grid = null;

    /**
     * Konvertuje zadane casti datumu na DB date format pro vyhledavani pomoci LIKE
     */
    protected function convertLocaleDateToDbDate(string $value): string
    {
        // Zjistime aktualni strukturu podle Locale
        $formatter = new IntlDateFormatter(Locale::getDefault(), IntlDateFormatter::SHORT, IntlDateFormatter::NONE, date_default_timezone_get(), IntlDateFormatter::GREGORIAN);
        $pattern = $formatter->getPattern();

        // Zjistime zvoleny separator a poradi dne a mesice
        $patternSeparators = ['.', '/', '-', ' '];
        $separator = null;
        foreach ($patternSeparators as $patternSeparator) {
            if (strpos($pattern, $patternSeparator)) {
                $splitPattern = str_split($pattern);
                $first = 'month';
                $second = 'day';
                $firstPatternChar = strtolower($splitPattern[0]);
                if ('d' == $firstPatternChar || 'j' == $firstPatternChar) {
                    $first = 'day';
                    $second = 'month';
                }
                $separator = $patternSeparator;
                break;
            }
        }

        if (null !== $separator) {
            $dateDb = [];

            // Pokud je datumem
            // https://bugs.php.net/bug.php?id=68528
            // IntlDateFormatter::parse() throws warnings on not parsable date on windows only
            if (false !== @$formatter->parse($value)) {
                $timestamp = $formatter->parse($value);

                $dateDb['day'] = date('d', $timestamp);
                $dateDb['month'] = date('m', $timestamp);
                $dateDb['year'] = date('Y', $timestamp);

                // je ve formatu napr. ".12.2014" nebo "12.2014"
            } elseif (
                preg_match('/^\\' . $separator . '\d{1,2}\\' . $separator . '\d{4}$/', $value, $matches)
                || preg_match('/^\d{1,2}\\' . $separator . '\d{4}$/', $value, $matches)
            ) {
                [$dateDb[$second], $dateDb['year']] = explode($separator, trim($matches[0], $separator));

                // je ve formatu napr. "24.12." nebo "24.12"
            } elseif(
                preg_match('/^\d{1,2}\\' . $separator . '\d{1,2}\\' . $separator . '$/', $value, $matches)
                || preg_match('/^\d{1,2}\\' . $separator . '\d{1,2}$/', $value, $matches)
            ) {
                [$dateDb[$first], $dateDb[$second]] = explode($separator, trim($matches[0], $separator));

                // je ve formatu napr. "2014"
            } elseif (preg_match('/^\d{4}$/', $value, $matches)) {
                $dateDb['year'] = $matches[0];

                // je ve formatu napr. ".12." nebo ".12"
            } elseif (preg_match('/^\\' . $separator . '\d{1,2}\\' . $separator . '$/', $value, $matches)
                || preg_match('/^\\' . $separator . '\d{1,2}$/', $value, $matches)) {
                if ('y' === $firstPatternChar) {
                    $dateDb[$first] = trim($matches[0], $separator);
                } else {
                    $dateDb[$second] = trim($matches[0], $separator);
                }

                // je ve formatu napr. "24."
            } elseif (preg_match('/^\d{1,2}\\' . $separator . '$/', $value, $matches)) {
                if ('y' === $firstPatternChar) {
                    $dateDb[$second] = trim($matches[0], $separator);
                } else {
                    $dateDb[$first] = trim($matches[0], $separator);
                }
            } else {
                $dateDb[$second] = trim($value);
            }

            // Pripravime date DB fragmenty z casti resultSet
            $string = '';
            if (isset($dateDb['year'])) {
                $string .= $dateDb['year'] . '-';
            }
            if (isset($dateDb['month'])) {
                if (!isset($dateDb['year'])) {
                    $string .= '-';
                }
                $string .= str_pad($dateDb['month'], 2, '0', STR_PAD_LEFT) . '-';
            }
            if (isset($dateDb['day'])) {
                if (!isset($dateDb['year']) && !isset($dateDb['month'])) {
                    $string .= '-';
                }
                $string .= str_pad($dateDb['day'], 2, '0', STR_PAD_LEFT);
            }

            return $string;
        }

        return $value;
    }

    /**
     * Get number of current page
     */
    #[\Override]
    public function getNumberOfPages(): int
    {
        $numberOfPages = (int) ceil($this->getCountOfItemsTotal() / $this->getGrid()->getNumberOfVisibleRows());

        if ($numberOfPages < 1) {
            $numberOfPages = 1;
        }

        return $numberOfPages;
    }

    /**
     * Return count of items
     */
    #[\Override]
    public function getCountOfItems(): int
    {
        return $this->countItems;
    }

    /**
     * Return count of items total
     */
    #[\Override]
    public function getCountOfItemsTotal(): int
    {
        return $this->countItemsTotal;
    }

    /**
     * Set grid instance
     */
    public function setGrid(?Grid $grid): self
    {
        $this->grid = $grid;

        return $this;
    }

    /**
     * Get grid instance
     */
    public function getGrid(): ?Grid
    {
        return $this->grid;
    }

    /**
     * Check if is prepared
     */
    public function isPrepared(): bool
    {
        return $this->isPrepared;
    }
}
