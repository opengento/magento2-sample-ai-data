<?php
/**
 * Copyright © OpenGento, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */

declare(strict_types=1);

namespace Opengento\SampleAiData\Provider;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    public const XML_PATH_API_KEY = 'opengento_sample_ai_data/general/api_key';
    public const XML_PATH_PROMPT = 'opengento_sample_ai_data/general/prompt';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function getApiKey(): string
    {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_API_KEY);
    }

    public function getPrompt(mixed $store): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PROMPT, ScopeInterface::SCOPE_STORES, $store);
    }
}
