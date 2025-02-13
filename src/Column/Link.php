<?php

namespace Lemo\JqGrid\Column;

use Lemo\JqGrid\AdapterInterface;
use Lemo\JqGrid\Exception;

use function array_keys;
use function array_values;
use function sprintf;
use function str_replace;

class Link extends AbstractColumn
{
    protected ?string $href = null;
    protected ?array $hrefParams = null;

    protected array $validTagAttributes = [
        'download' => true,
        'href' => true,
        'hreflang' => true,
        'media' => true,
        'ping' => true,
        'referrerpolicy' => true,
        'rel' => true,
        'target' => true,
        'type' => true,
    ];

    #[\Override]
    public function renderValue(AdapterInterface $adapter, array $rowData): string
    {
        $href = $this->getHref();
        $hrefParams = $this->getHrefParams();
        $value = (string) $this->getValue();

        if (null === $href) {
            throw new Exception\RuntimeException(
                "Href is not set."
            );
        }

        if (null !== $hrefParams) {

            // Upravime parametry
            foreach ($hrefParams as $keyRoute => $keyData) {
                if (array_key_exists($keyData, $rowData)) {
                    $hrefParams[$keyRoute] = $rowData[$keyData];
                } else {
                    throw new Exception\RuntimeException("Key '$keyData' was not found in row data.");
                }
            }

            $href = str_replace(
                array_keys($hrefParams),
                array_values($hrefParams),
                $href
            );
        }

        $attributes = $this->getAttributes();
        $attributes['href'] = $href;

        return sprintf(
            '<a %s>%s</a>',
            $this->createAttributesString($attributes),
            $value
        );
    }

    public function setHref(?string $href): self
    {
        $this->href = $href;

        return $this;
    }

    public function getHref(): ?string
    {
        return $this->href;
    }

    public function setHrefParams(?array $hrefParams): self
    {
        $this->hrefParams = $hrefParams;

        return $this;
    }

    public function getHrefParams(): ?array
    {
        return $this->hrefParams;
    }
}
