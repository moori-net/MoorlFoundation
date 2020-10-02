<?php

namespace MoorlFoundation\Core;

use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;

class PluginFoundation
{
    /**
     * @var DefinitionInstanceRegistry
     */
    private $definitionInstanceRegistry;

    public function __construct(
        DefinitionInstanceRegistry $definitionInstanceRegistry
    )
    {
        $this->definitionInstanceRegistry = $definitionInstanceRegistry;
    }
}