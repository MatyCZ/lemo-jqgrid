<?php

declare(strict_types=1);

namespace Lemo\JqGrid\Storage\Laminas;

use ArrayIterator;
use Laminas\Session\Container as SessionContainer;
use Lemo\JqGrid\StorageInterface;

class SessionStorage implements StorageInterface
{
    /**
     * Default session namespace
     */
    final public const NAMESPACE_DEFAULT = 'Lemo_JqGrid';

    /**
     * Session namespace
     */
    protected string $namespace = self::NAMESPACE_DEFAULT;

    /**
     * Object to proxy $_SESSION storage
     */
    protected SessionContainer $session;

    /**
     * Sets session storage options and initializes session namespace object
     */
    public function __construct(?string $namespace = null)
    {
        if (null !== $namespace) {
            $this->namespace = $namespace;
        }

        $this->session = new SessionContainer($this->namespace);
    }

    #[\Override]
    public function clear(string $gridName): self
    {
        $this->session->offsetUnset($gridName);

        return $this;
    }

    #[\Override]
    public function exists(string $gridName): bool
    {
        return $this->session->offsetExists($gridName);
    }

    #[\Override]
    public function read(string $gridName): ArrayIterator
    {
        /** @var ArrayIterator|null $params */
        $params = $this->session->offsetGet($gridName);

        return $params ?? new ArrayIterator();
    }

    #[\Override]
    public function write(string $gridName, ArrayIterator $params): self
    {
        $this->session->offsetSet($gridName, $params);

        return $this;
    }
}
