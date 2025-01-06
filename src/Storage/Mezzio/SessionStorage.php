<?php

declare(strict_types=1);

namespace Lemo\JqGrid\Storage\Mezzio;

use ArrayIterator;
use Lemo\JqGrid\StorageInterface;
use Mezzio\Session\SessionInterface;

class SessionStorage implements StorageInterface
{
    protected SessionInterface $session;

    /**
     * Sets session storage options and initializes session namespace object
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    #[\Override]
    public function clear(string $gridName): self
    {
        $this->session->clear($gridName);

        return $this;
    }

    #[\Override]
    public function exists(string $gridName): bool
    {
        return $this->session->has($gridName);
    }

    #[\Override]
    public function read(string $gridName): ArrayIterator
    {
        /** @var array|null $params */
        $params = $this->session->get($gridName);

        return new ArrayIterator($params ?? []);
    }

    #[\Override]
    public function write(string $gridName, ArrayIterator $params): self
    {
        $this->session->set($gridName, $params->getArrayCopy());

        return $this;
    }
}
