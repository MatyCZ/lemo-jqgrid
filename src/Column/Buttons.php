<?php

declare(strict_types=1);

namespace Lemo\JqGrid\Column;

use Lemo\JqGrid\AdapterInterface;
use Lemo\JqGrid\ButtonInterface;
use Mezzio\Helper\UrlHelper;

use function implode;

class Buttons extends AbstractColumn
{
    /**
     * @var ButtonInterface[]|null
     */
    protected ?array $buttons = null;
    protected string $separator = '&nbsp;';

    public function __construct(
        string $name,
        protected ?UrlHelper $urlHelper = null
    ) {
        parent::__construct($name);

        $this->getAttributes()->setIsSortable(false);
        $this->getAttributes()->setIsSearchable(false);
    }

    #[\Override]
    public function renderValue(AdapterInterface $adapter, array $rowData): string
    {
        $buttons = $this->getButtons();

        if (null === $buttons) {
            return '';
        }

        $parts = [];
        foreach ($buttons as $button) {
            if (true === $button->isValid($adapter, $rowData)) {
                $parts[] = $button->render($rowData);
            }
        }

        return implode($this->getSeparator(), $parts);
    }

    public function addButton(ButtonInterface $button): self
    {
        $this->buttons[] = $button;

        return $this;
    }

    /**
     * @param ButtonInterface[]|null $buttons
     */
    public function setButtons(?array $buttons): self
    {
        if (null === $buttons) {
            $this->buttons = null;

            return $this;
        }

        foreach ($buttons as $button) {
            $this->addButton($button);
        }

        return $this;
    }

    /**
     * @return ButtonInterface[]|null
     */
    public function getButtons(): ?array
    {
        return $this->buttons;
    }

    public function setSeparator(string $separator): self
    {
        $this->separator = $separator;

        return $this;
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }

    public function setUrlHelper(?UrlHelper $urlHelper): self
    {
        $this->urlHelper = $urlHelper;

        return $this;
    }

    public function getUrlHelper(): ?UrlHelper
    {
        return $this->urlHelper;
    }
}
