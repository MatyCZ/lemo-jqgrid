<?php

declare(strict_types=1);

namespace Lemo\JqGrid\Column;

use DateTime;
use Lemo\JqGrid\AdapterInterface;
use Lemo\JqGrid\ColumnAttributes;

use function array_keys;
use function array_values;
use function count;
use function explode;
use function implode;
use function is_array;
use function krsort;
use function preg_match;
use function preg_match_all;
use function preg_replace;
use function str_contains;
use function str_ends_with;
use function str_replace;
use function str_starts_with;
use function strlen;
use function substr;
use function trim;

class Concat extends AbstractColumn
{
    public function __construct(
        string $name,
        protected ConcatOptions $options = new ConcatOptions()
    ) {
        parent::__construct($name);

        $this->getAttributes()->setSearchOperators(ColumnAttributes::SEARCH_OPERATORS_TEXT);
    }

    public function getOptions(): ConcatOptions
    {
        return $this->options;
    }

    #[\Override]
    public function renderValue(AdapterInterface $adapter, array $rowData): ?string
    {
        $patternValues = [];

        foreach ($this->getOptions()->getIdentifiers() as $index => $identifier) {
            $valuesIdentifier = $adapter->findValue($identifier, $rowData);

            if (!empty($valuesIdentifier)) {

                if (!is_array($valuesIdentifier)) {
                    $valuesIdentifier = [$valuesIdentifier];
                }

                foreach ($valuesIdentifier as $valueIndex => $valueIdentifier) {
                    if ($valueIdentifier instanceof DateTime) {
                        $valueIdentifier = $valueIdentifier->format('Y-m-d H:i:s');
                    }

                    $patternValues[$valueIndex]['%s' . $index] = $valueIdentifier;
                }
            }
        }

        // Slozime jednotlive casti na radak
        foreach ($patternValues as $patternValue) {
            $values[] = $this->patternEvaluate(
                $this->getOptions()->getPattern(),
                $patternValue
            );
        }

        if (empty($values)) {
            return null;
        }

        return implode($this->getOptions()->getSeparator(), $values);
    }

    protected function patternEvaluate(string $pattern, array $patternValues): string
    {
        $value = '';

        if (!empty($patternValues)) {
            $patternValuesToReplace = [];

            // Odstranime hodnoty casti patternu, ktere nemaji prazdnou hodnotu
            foreach ($patternValues as $key => $patternValue) {
                if ('' == $patternValue) {
                    unset($patternValues[$key]);
                }
            }

            // Pridame indexy jednotlivym znakum pro nahrazeni
            $patternExploded = explode('%s', $pattern);
            $pattern = '';
            foreach ($patternExploded as $index => $pat) {
                $prefix = '';
                if ($index > 0) {
                    $prefix = '%s' . ($index - 1);

                    // Vytvorime hodnoty k nahrazeni, vcetne prazdneho stringu
                    $patternValuesToReplace[$prefix] = $patternValues[$prefix] ?? ' ';
                }

                $pattern .= $prefix . $pat;
            }

            // Nacteme jednotlive casti patternu (dle zavorek)
            if (!str_contains($pattern, '(')) {
                $matches[] = $pattern;
            } else {
                preg_match_all('~(?= ( \( (?> [^()]++ | (?1) )* \) ) )~x', $pattern, $matches);
                $matches = $matches[1];
            }

            // Seradime klice obracene, aby se nahrazovaly odzadu (od nejvyssiho zanoreni)
            krsort($matches);

            // Najdeme si casti, ktere maji jen jednu cast k nahrazeni a nebyl nacten retezec
            $partsValues = [];
            foreach ($matches as $match) {
                $matchOriginal = $match;

                // Nahradime v aktualni casti vyrazi, ktere jsou jiz vyhodnocene
                $match = str_replace(
                    array_keys($partsValues),
                    array_values($partsValues),
                    (string) $match
                );

                // Rozdelime si vyraz na dvojice se separatorem
                $parts = $this->patternEvaluateParse($match);

                // Projdeme dvojice a vyhodnotime, zda maji oba vyrazy
                foreach ($parts as $part) {
                    $part = (string) $part;

                    // Doplnime do patternu casti hodnoty
                    $partValue = str_replace(
                        array_keys($patternValues),
                        array_values($patternValues),
                        $part
                    );

                    // Zjistime si pocet nahrazenych znaku a nenahrazenych znaku
                    preg_match_all('/%s[0-9]{1}?/', $partValue, $partExpressionsNotReplaced);
                    preg_match_all('/%s[0-9]{1}?/', $part, $partExpressions);

                    // Odstranime separator mezi 2 castmi
                    if (count($partExpressionsNotReplaced[0]) > 0) {

                        // Muzeme odstranit separator?
                        if (preg_match('/{(.*)}?/', $part)) {

                            // Odstranime cast patternu, ktera predchazi {}
                            $separatorPattern = substr($pattern, strpos($pattern, $part));
                            $separatorPattern = substr($separatorPattern, strpos($separatorPattern, '}') + 1);

                            // Doplnime do paternu separatoru hodnoty
                            $separatorValue = str_replace(array_keys($patternValues), array_values($patternValues), $separatorPattern);

                            // Zjistime si pocet nahrazenych znaku a nenahrazenych znaku
                            preg_match_all('/%s[0-9]{1}?/', $separatorPattern, $separatorExpressions);
                            preg_match_all('/%s[0-9]{1}?/', $separatorValue, $separatorExpressionsNotReplaced);

                            // Pokud nebyla nahrazena zadna hodnota, odstranime separator
                            if (count($separatorExpressions[0]) == count($separatorExpressionsNotReplaced[0])) {
                                $match = str_replace($part, implode('', $partExpressions[0]), $match);
                            } else {
                                $match = str_replace($partExpressionsNotReplaced[0], '', $match);
                            }
                        } else {
                            $match = str_replace($part, implode('', $partExpressions[0]), $match);
                        }
                    }
                }

                // Odstranime z patternu znaky, ktere urciji neodlucitelny separator
                $match = str_replace(['{', '}'], '', $match);

                // Zjistime, zda ma cast zavorky a pokud ano, tak je odstranime
                $partPattern = $match;
                $partHasBrackets = false;
                if (str_starts_with($partPattern, '(') && str_ends_with($partPattern, ')')) {
                    if (str_starts_with($partPattern, '(')) {
                        $partPattern = substr($partPattern, 1);
                    }
                    if (str_ends_with($partPattern, ')')) {
                        $partPattern = substr($partPattern, 0, -1);
                    }

                    $partHasBrackets = true;
                }

                // Vytvorime hodnotu pro cast
                $valueWithRealData = str_replace(array_keys($patternValues), array_values($patternValues), $partPattern);
                $value = str_replace(array_keys($patternValuesToReplace), array_values($patternValuesToReplace), $partPattern);
                $value = trim($value);
                $value = preg_replace('~\s+~u', ' ', $value);

                // Zjistime, zda byly nahrazene veskere hodnoty
                preg_match_all('/%s[0-9]{1}?/', $valueWithRealData, $valueNotReplacedExpressions);
                preg_match_all('/%s[0-9]{1}?/', $match, $valueExpressions);

                if (
                    count($valueNotReplacedExpressions[0]) != count($valueExpressions[0])
                    && true === $partHasBrackets
                    && '' !== $value
                ) {
                    $value = '(' . $value . ')';
                }

                $partsValues = array_merge([$matchOriginal => $value], $partsValues);
            }

            $value = str_replace(array_keys($partsValues), array_values($partsValues), $pattern);
            $value = str_replace(array_keys($patternValuesToReplace), array_values($patternValuesToReplace), $value);
            $value = trim($value);
            $value = preg_replace('~\s+~u', ' ', $value);
        }

        return $value;
    }

    protected function patternEvaluateParse(string $def): array
    {
        $currentPos = 0;
        $length = strlen($def);

        $parts = [];
        while ($currentPos < $length) {
            preg_match(
                '/(%s[0-9]{1,2})(.*?)(%s[0-9]{1,2})/',
                $def,
                $matches,
                0,
                $currentPos
            );

            if (isset($matches[0])) {
                $parts[] = $matches[0];
                $currentPos += strlen($matches[1] . $matches[2]);
            } else {
                break;
            }
        }

        return $parts;
    }
}
