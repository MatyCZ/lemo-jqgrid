<?php

declare(strict_types=1);

namespace Lemo\JqGrid;

final class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'view_helpers' => $this->getViewHelpersConfig(),
        ];
    }

    public function getViewHelpersConfig(): array
    {
        return [
            'aliases' => [
                'jqGrid' => View\Helper\JqGrid::class,
            ],
            'invokables' => [
                View\Helper\JqGrid::class => View\Helper\JqGrid::class,
            ],
        ];
    }
}
