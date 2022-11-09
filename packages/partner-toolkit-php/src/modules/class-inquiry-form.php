<?php

namespace SteinRein\Partner\Modules;

class Inquiry_Form extends Module
{
    public array $configurable = [
        'api_key',
        'partner_id',
        'lang',
    ];

    public function __construct(array $configuration)
    {
        parent::__construct($configuration);
        $this->init();
    }

    public function init()
    {
        parent::init();

    }
}
