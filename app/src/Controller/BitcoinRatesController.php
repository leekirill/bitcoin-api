<?php

namespace App\Controller;

use App\Service\BitcoinRatesService;
use App\Exception\ErrorMessages;
use App\DTO\HistoryRequestDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BitcoinRatesController extends AbstractController
{
    public function __construct(
        private readonly BitcoinRatesService $ratesService,
        private readonly string $timezone
    ) {} 

    #[Route('/api/rates', methods: ['GET'])]
    public function getRate(): JsonResponse
    {
        try {
            $rates = $this->ratesService->getCurrentRates();
            return new JsonResponse($rates);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/api/rates/history', methods: ['GET'])]
    public function history(
        Request $request,
        ValidatorInterface $validator
    ): JsonResponse {
        try {
            $dto = new HistoryRequestDTO();
            $dto->range = $request->query->get('range');
            $dto->from = $request->query->get('from');
            $dto->to = $request->query->get('to');

            $errors = $validator->validate($dto);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return new JsonResponse(['errors' => $errorMessages], 400);
            }

            if ($dto->range) {
                $to = new \DateTimeImmutable('now', new \DateTimeZone($this->timezone));
                $from = match ($dto->range) {
                    '1h' => $to->modify('-1 hour'),
                    '24h' => $to->modify('-24 hours'),
                    default => throw new \Exception(ErrorMessages::INVALID_PARAMETERS),
                };
            } else {
                $from = new \DateTimeImmutable($dto->from, new \DateTimeZone($this->timezone));
                $to = new \DateTimeImmutable($dto->to, new \DateTimeZone($this->timezone));
            }

            $result = $this->ratesService->getHistoricalRates($from, $to);
            return new JsonResponse($result);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => ErrorMessages::UNDEFINED_ERROR], 500);
        }
    }
}
