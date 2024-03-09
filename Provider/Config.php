<?php
/**
 * Copyright © OpenGento, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */

declare(strict_types=1);

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    public const XML_PATH_API_KEY = 'opengento/sample_ai_data/api_key';
    public const XML_PATH_PROMPT = 'opengento/sample_ai_data/prompt';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function getApiKey(mixed $store): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_API_KEY, ScopeInterface::SCOPE_STORES, $store);
    }

    public function getPrompt(mixed $store): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PROMPT, ScopeInterface::SCOPE_STORES, $store);
    }
}