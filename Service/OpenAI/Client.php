<?php

declare(strict_types=1);

class Client
{
    /**
     * @var OpenAi
     */
    private $openAi;

    private $apiKey = null;


    /**
     * @param string $apiKey
     */
    public function __construct(
        OpenAi
    ) {

    }

    private function getApiKey(): string
    {
        if ($this->apiKey === null) {

            //@todo : get the apikey with the config
            //$this->apiKey = $this->config->getOpenAIApiKey();

            $this->apiKey = '';
        }

        return $this->apiKey;
    }

    /**
     * @param string $prompt
     * @throws \JsonException
     * @return
     */
    public function generateText(string $prompt)
    {
        $apiKey = $this->getApiKey();

        if (empty($apiKey)) {
            throw new \Exception('No API Key found in the configuration. Please provide your key');
        }

        $this->openAi = OpenAI::client($apiKey);

        $params = [
            'model' => "gpt-3.5-turbo-instruct", //@todo, let the user select what to use
            'prompt' => $prompt,
            'temperature' => 0.3,
            'max_tokens' => 1000,
            'top_p' => 1.0,
            'frequency_penalty' => 0.0,
            'presence_penalty' => 0.0,
        ];

        $response = $this->openAi->completions()->create($params);


        $json = json_decode($complete, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($json)) {
            return new Choice('');
        }

        if (isset($json['error'])) {
            $msg = 'OpenAI Error: ' . $json['error']['message'] . '[' . $json['error']['code'] . ']';
            throw new \Exception($msg);
        }

        if (!isset($json['choices'])) {
            throw new \Exception('No choices found in OpenAI response.');
        }

        $choices = $json['choices'];

        if (!is_array($choices) || count($choices) <= 0) {
            return new Choice('');
        }

        if (!isset($choices[0]['text'])) {
            return new Choice('');
        }

        $choiceData = $choices[0];

        $text = trim($choiceData['text']);

        return new Choice($text);
    }

}
