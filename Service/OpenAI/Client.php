<?php

declare(strict_types=1);

namespace Opengento\SampleAiData\Service\OpenAI;

use OpenAI;
use OpenAI\Client as OpenAiClient;
use Opengento\SampleAiData\Provider\Config;

class Client
{
    private ?OpenAiClient $openAiClient = null;

    private ?string $apiKey = null;

    /**
     * @param Config $config
     */
    public function __construct(
        private readonly Config $config
    ) {}

    private function getApiKey(): string
    {
        if ($this->apiKey === null) {
            $this->apiKey = $this->config->getApiKey();
        }

        return $this->apiKey;
    }

    private function getOpenAiClient(): OpenAiClient
    {
        if ($this->openAiClient === null) {
            $apiKey = $this->getApiKey();

            if (empty($apiKey)) {
                throw new \Exception('No API Key found in the configuration. Please provide your key');
            }

            $this->openAiClient = OpenAI::client($apiKey);
        }

        return $this->openAiClient;
    }

    /**
     * @param string $prompt
     * @return array
     * @throws \Exception
     */
    public function getResults(string $prompt): array
    {
        $this->openAiClient = $this->getOpenAiClient();

        $params = [
            'model' => "gpt-3.5-turbo-instruct", //@todo, let the user select what to use
            'prompt' => $prompt,
            'temperature' => 0.3,
            'max_tokens' => 1000,
            'top_p' => 1.0,
            'frequency_penalty' => 0.0,
            'presence_penalty' => 0.0,
        ];

        $response = $this->openAiClient->completions()->create($params);

        if (isset($response->error)) {
            $msg = 'OpenAI Error: ' . $response->error['message'] . '[' . $response->error['code'] . ']';
            throw new \Exception($msg);
        }

        if (!isset($response->choices)) {
            throw new \Exception('No choices found in OpenAI response.');
        }

        $choices = $response->choices;

        if (!is_array($choices) || count($choices) <= 0) {
            return '';
        }

        if (!isset($choices[0]->text)) {
            return '';
        }

        $choiceData = $choices[0];

        return $this->toArray(trim($choiceData->text));
    }

    public function generateImage(string $prompt): string
    {
        $this->openAiClient = $this->getOpenAiClient();

        $params = [
            'model' => 'dall-e-2',
            'prompt' => $prompt,
            'n' => 1,
            'size' => '1024x1024',
            'response_format' => 'url',
        ];

        $response = $this->openAiClient->images()->create($params);

        if (!is_array($response->data) || count($response->data) <= 0) {
            return '';
        }

        if (!isset($response->data[0]->url)) {
            return '';
        }

        $imageData = $response->data[0];

        return trim($imageData->url);
    }

    private function toArray(string $string): array
    {
        $result = [];
        foreach (explode(PHP_EOL, $string) as $k => $line) {
            $result[$k] = explode(';', $line);
        }
        return $result;
    }
}
