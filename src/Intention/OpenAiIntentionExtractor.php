<?php

declare(strict_types=1);

namespace App\Intention;

use App\Intention\DTO\Intention;
use App\Intention\DTO\IntentionType;
use App\Intention\Exception\IntentionParserException;
use JsonException;
use OpenAI;
use OpenAI\Contracts\ClientContract;

use Psr\Log\LoggerInterface;

use function file_get_contents;
use function json_decode;

use const JSON_THROW_ON_ERROR;

final class OpenAiIntentionExtractor implements IntentionExtractor
{
    public static function fromToken(string $apiKey, LoggerInterface $logger): self
    {
        $client = OpenAI::client($apiKey);

        return new OpenAiIntentionExtractor($client, $logger);
    }

    public function __construct(
        private readonly ClientContract $client,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function parse(string $message): Intention
    {
        $this->logger->debug('Parsing message: {message}', ['message' => $message]);
        $prompt = file_get_contents(__DIR__ . '/Resources/prompt.txt');

        $result = $this->client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => $prompt],
                ['role' => 'user', 'content' => $message],
            ],
        ]);

        $intentionResp = $result['choices'][0]['message']['content'];
        $this->logger->debug('Response from the backend: {response}', ['response' => $intentionResp]);
        try {
            $intentionData = json_decode($intentionResp, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->logger->error('Error parsing response from the backend: {exception}', ['exception' => $e]);

            throw new IntentionParserException('Invalid response from the backend!');
        }

        if (isset($intentionData['error'])) {
            throw new IntentionParserException($intentionData['error']);
        }

        $command = $intentionData['command'] ?? '';
        $type = IntentionType::tryFrom($command);
        if ($type === null) {
            throw new IntentionParserException('Unknown command!');
        }

        if ($type === IntentionType::AddExpense) {
            $amount = $intentionData['amount'] ?? 0;
            if ($amount === 0) {
                throw new IntentionParserException('Amount is not set!');
            }
        } else {
            $amount = 0;
        }

        return new Intention($type, $amount);
    }
}

