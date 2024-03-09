<?php


declare(strict_types=1);

namespace Opengento\SampleAiData\Service\Generator;

use Opengento\SampleAiData\Service\OpenAI\Client as OpenAiClient;

class ProductGenerator
{
    public function __construct(
        private readonly OpenAiClient $openAiClient
    ) {}

    public function generate(string $keywords, int $maxCount, string $category, string $salesChannel, int $descriptionLength)
    {
        $prompt = 'Create a list of demo products with these properties, separated values with ";". Only write down values and no property names ' . PHP_EOL;
        $prompt .= PHP_EOL;
        $prompt .= 'the following properties should be generated.' . PHP_EOL;
        $prompt .= 'Every resulting line should be in the order and sort provided below:' . PHP_EOL;
        $prompt .= PHP_EOL;
        $prompt .= 'product count.' . PHP_EOL;
        $prompt .= 'product number code. should be 16 unique random alphanumeric.' . PHP_EOL;
        $prompt .= 'name of the product.' . PHP_EOL;
        $prompt .= 'description (about ' . $descriptionLength . ' characters).' . PHP_EOL;
        $prompt .= 'price value (no currency just number).' . PHP_EOL;
        $prompt .= 'EAN code.' . PHP_EOL;
        $prompt .= 'SEO description (max 100 characters).' . PHP_EOL;
        $prompt .= PHP_EOL;
        $prompt .= 'Please only create this number of products: ' . $maxCount . PHP_EOL;
        $prompt .= PHP_EOL;
        $prompt .= 'The industry of the products should be: ' . $keywords;


        $choice = $this->openAiClient->generateText($prompt);

        $text = $choice->getText();



    }
}
