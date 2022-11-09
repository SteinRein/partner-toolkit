<?php

namespace SteinRein\Partner\Modules;

class Module
{
    public bool $is_configured = false;
    public bool $is_initialized = false;
    public array $configuration = [];
    public array $configurable = [];

    public function __construct(array $configuration)
    {
        $this->configuration = $this->configure($configuration);
    }

    public function configure(array $configuration)
    {
        $configurable = array_values($this->configurable);
        $configuration = array_intersect_key($configuration, array_flip($configurable));

        $this->configuration = $configuration;
        $this->is_configured = true;
    }

    public function init()
    {
        $this->is_initialized = true;
    }
}
