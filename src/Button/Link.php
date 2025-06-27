<?php

declare(strict_types=1);

namespace Lemo\JqGrid\Button;

use Lemo\JqGrid\Exception;

use function sprintf;

class Link extends AbstractButton
{
    protected ?string $href = null;

    /**
     * @var callable|null
     */
    protected $hrefCallback = null;

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
    public function render(array $rowData): string
    {
        if (null !== $this->hrefCallback && is_callable($this->hrefCallback)) {
            $href = call_user_func($this->hrefCallback, $rowData);
        } else {
            $href = $this->getHref();

            if (null === $href) {
                throw new Exception\RuntimeException("Href is not set.");
            }
        }

        $attributes = $this->getAttributes();
        $attributes['href'] = $href;

        return sprintf(
            '<a %s>%s</a>',
            $this->createAttributesString($attributes),
            $this->getContent()
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

    public function setHrefCallback(callable $callback): self
    {
        $this->hrefCallback = $callback;

        return $this;
    }

    public function getHrefCallback(): ?callable
    {
        return $this->hrefCallback;
    }
}
