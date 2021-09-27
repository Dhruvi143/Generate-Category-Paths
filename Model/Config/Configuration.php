<?php

namespace Codilar\CategoryForGTM\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Configuration{
    const LOGGER_ENABLE = 'catalog_configuration/catalog/categoryPath_enable';
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }
    public function getLoggerStatus()
    {
        return !!$this->scopeConfig->getValue(self::LOGGER_ENABLE);
    }
}
