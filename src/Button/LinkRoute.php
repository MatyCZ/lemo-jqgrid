<?php

declare(strict_types=1);

namespace Lemo\JqGrid\Button;

use Lemo\JqGrid\Exception;
use Mezzio\Helper\UrlHelper;
use Mezzio\Router\RouteResult;

use function array_merge;
use function sprintf;
use function urldecode;

class LinkRoute extends AbstractButton
{
    protected ?string $routeName = null;
    protected ?array $routeParams = null;
    protected bool $routeReuseMatchedParams = false;
    protected ?UrlHelper $urlHelper = null;

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
        $attributes = $this->getAttributes();
        $attributes['href'] = $this->generateHref($rowData);

        return sprintf(
            '<a %s>%s</a>',
            $this->createAttributesString($attributes),
            $this->getContent()
        );
    }

    protected function generateHref(array $rowData): string
    {
        $urlHelper = $this->getUrlHelper();

        if (null === $urlHelper) {
            throw new Exception\RuntimeException(
                sprintf(
                    "No instance of %s provided",
                    UrlHelper::class
                )
            );
        }

        if (null === $urlHelper->getRouteResult()) {
            throw new Exception\RuntimeException("No route result found");
        }

        $routeName = $this->getRouteName();
        $routeParams = $this->getRouteParams();
        $routeReuseMatchedParams = $this->getRouteReuseMatchedParams();

        // Upravime parametry
        foreach ($routeParams as $keyRoute => $keyData) {
            if (array_key_exists($keyData, $rowData)) {
                $routeParams[$keyRoute] = $rowData[$keyData];
            } else {
                throw new Exception\RuntimeException("Key '$keyData' was not found in row data.");
            }
        }

        $routeResult = $urlHelper->getRouteResult();

        if (null === $routeName) {
            if (null === $routeResult) {
                throw new Exception\RuntimeException(
                    sprintf(
                        "No instance of %s provided.",
                        RouteResult::class
                    )
                );
            }

            $routeName = $routeResult->getMatchedRouteName();

            if (null === $routeName) {
                throw new Exception\RuntimeException('RouteResult does not contain a matched route name.');
            }
        }

        if (true === $routeReuseMatchedParams && null !== $routeResult) {
            $routeResultParams = $routeResult->getMatchedParams();

            $routeParams = array_merge($routeResultParams, $routeParams);
        }

        // Add href attribute
        return urldecode(
            $urlHelper->generate(
                $routeName,
                $routeParams
            )
        );
    }

    public function setRouteName(?string $routeName): self
    {
        $this->routeName = $routeName;

        return $this;
    }

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    public function setRouteParams(?array $routeParams): self
    {
        $this->routeParams = $routeParams;

        return $this;
    }

    public function getRouteParams(): ?array
    {
        return $this->routeParams;
    }

    public function setRouteReuseMatchedParams(bool $routeReuseMatchedParams): self
    {
        $this->routeReuseMatchedParams = $routeReuseMatchedParams;

        return $this;
    }

    public function getRouteReuseMatchedParams(): bool
    {
        return $this->routeReuseMatchedParams;
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
