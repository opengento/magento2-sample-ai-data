<?php


declare(strict_types=1);

namespace Opengento\SampleAiData\Service\Generator;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\State;
use Opengento\SampleAiData\Service\OpenAI\Client as OpenAiClient;

class ImageGenerator
{
    public function __construct(
        private readonly OpenAiClient $openAiClient,
    ) {}

    public function generate()
    {
    }
}
