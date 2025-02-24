<?php

namespace App\Service;

use App\Repository\BitcoinRatesRepository;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Validator\Constraints\NotNull;
use Exception;

class BitcoinRatesService
{
    public function __construct(
        private readonly BitcoinRatesRepository $repository,
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly string $apiUrl,
        private readonly string $timezone
    ) {}

    #[ArrayShape([
        'timestamp' => 'string',
        'rates' => 'array<string, string>'
    ])]
    public function getCurrentRates(): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->apiUrl);
            $data = $response->toArray();

            if (!isset($data['bitcoin'])) {
                throw new Exception('Invalid API response format');
            }

            $rates = [];
            foreach ($data['bitcoin'] as $currency => $rate) {
                $rates[strtoupper($currency)] = (string) $rate;
            }

            return [
                'timestamp' => (new \DateTimeImmutable('now', new \DateTimeZone($this->timezone)))->format(DATE_ATOM),
                'rates' => $rates,
            ];
        } catch (Exception $e) {
            $this->logger->error('Failed to fetch current rates', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception('Failed to fetch current rates: ' . $e->getMessage());
        }
    }

    #[ArrayShape([
        'from' => 'string',
        'to' => 'string',
        'history' => 'array'
    ])]
    public function getHistoricalRates(
        #[NotNull] \DateTimeImmutable $from,
        #[NotNull] \DateTimeImmutable $to
    ): array {
        try {
            $history = $this->repository->findHistory($from, $to);

            if (empty($history)) {
                return [
                    'from' => $from->format(DATE_ATOM),
                    'to' => $to->format(DATE_ATOM),
                    'history' => []
                ];
            }

            $formattedHistory = [];
            foreach ($history as $rate) {
                $formattedHistory[$rate->getTimestamp()->format(DATE_ATOM)][$rate->getCurrency()] = $rate->getRate();
            }

            $responseData = [];
            foreach ($formattedHistory as $timestamp => $rates) {
                $responseData[] = [
                    'timestamp' => $timestamp,
                    'rates' => $rates,
                ];
            }

            return [
                'from' => $from->format(DATE_ATOM),
                'to' => $to->format(DATE_ATOM),
                'history' => $responseData,
            ];
        } catch (Exception $e) {
            $this->logger->error('Failed to fetch historical rates', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception('Failed to fetch historical rates: ' . $e->getMessage());
        }
    }
} 