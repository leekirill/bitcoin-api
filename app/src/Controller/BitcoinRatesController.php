<?php

namespace App\Controller;

use App\Repository\BitcoinRatesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use DateTimeImmutable;
use DateTimeZone;

class BitcoinRatesController extends AbstractController
{

    private HttpClientInterface $httpClient;
    private string $apiUrl;

    public function __construct(HttpClientInterface $httpClient, string $apiUrl)
    {
        $this->httpClient = $httpClient;
        $this->apiUrl = $apiUrl;
    }


    #[Route('/api/rates', name: 'get_rate', methods: ['GET'])]
    public function getRate(): JsonResponse
    {
        try {
            $response = $this->httpClient->request('GET', $this->apiUrl);
            $data = $response->toArray();
    
            $rates = [];
            foreach ($data['bitcoin'] as $currency => $rate) {
                $rates[strtoupper($currency)] = (string) $rate;
            }
    
            $formattedResponse = [
                'timestamp' => (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format(DATE_ATOM),
                'rates' => $rates,
            ];
    
            return new JsonResponse($formattedResponse);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Unable to fetch rates'], 500);
        }
    }
    

    #[Route('/api/rates/history', methods: ['GET'])]
    public function history(Request $request, BitcoinRatesRepository $rateRepository): JsonResponse
    {
        try {
            $range = $request->query->get('range');
            $from = $request->query->get('from');
            $to = $request->query->get('to');
        
            if ($range) {
                $to = new DateTimeImmutable('now', new DateTimeZone('UTC'));
                switch ($range) {
                    case '1h':
                        $from = $to->modify('-1 hour');
                        break;
                    case '24h':
                        $from = $to->modify('-24 hours');
                        break;
                    default:
                        return $this->json(['error' => 'Invalid range'], 400);
                }
            } elseif ($from && $to) {
                $from = new DateTimeImmutable($from, new DateTimeZone('UTC'));
                $to = new DateTimeImmutable($to, new DateTimeZone('UTC'));
            } else {
                return $this->json(['error' => 'Invalid parameters'], 400);
            }
        
            $history = $rateRepository->findHistory($from, $to);
        
            $formattedHistory = [];
            foreach ($history as $rate) {
                $formattedHistory[$rate->getTimestamp()->format(DATE_ATOM)][$rate->getCurrency()] = $rate->getRate();
            }
        
            $response = [];
            foreach ($formattedHistory as $timestamp => $rates) {
                $response[] = [
                    'timestamp' => $timestamp,
                    'rates' => $rates
                ];
            }
    
            $formattedResponse = [
                'from' => $from->format(DATE_ATOM),
                'to' => $to->format(DATE_ATOM),
                'history' => $response
            ];
        
            return new JsonResponse($formattedResponse);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Unable to fetch rates'], 500);
        }
    }
    
}
