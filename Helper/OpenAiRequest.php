<?php

namespace Opengento\SampleAiData\Helper;

use Opengento\SampleAiData\Service\OpenAI\Client;

class OpenAiRequest
{
    public function __construct(
        private readonly Client $openAiClient
    )
    {
    }

    public function send($prompt) {
        $choice = $this->openAiClient->generateText($prompt);
        return $this->toArray($choice);
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
