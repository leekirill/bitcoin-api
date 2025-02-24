<?php

namespace App\Command;

use App\Entity\BitcoinRates;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(name: 'app:update-rate')]
#[AsPeriodicTask('1 minute', schedule: 'default')]
class UpdateBitcoinRatesCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private HttpClientInterface $httpClient;
    private string $apiUrl;
    private array $currencies;

    public function __construct(
        EntityManagerInterface $entityManager, 
        HttpClientInterface $httpClient,
        string $apiUrl,
        string $currencies
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->httpClient = $httpClient;
        $this->apiUrl = $apiUrl;
        $this->currencies = array_map('strtolower', explode(',', $currencies));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        try {
            $response = $this->httpClient->request('GET', $this->apiUrl);
            $data = $response->toArray();
    
            if (!isset($data['bitcoin'])) {
                $output->writeln('Failed to fetch rates.');
                return Command::FAILURE;
            }
    
            $timestamp = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
            foreach ($this->currencies as $currency) {
                if (isset($data['bitcoin'][$currency])) {
                    $rateEntity = new BitcoinRates();
                    $rateEntity->setTimestamp($timestamp);
                    $rateEntity->setCurrency(strtoupper($currency));
                    $rateEntity->setRate((string)$data['bitcoin'][$currency]);
                    $this->entityManager->persist($rateEntity);
                }
            }
            $this->entityManager->flush();
    
            $output->writeln(sprintf('Rates updated at %s', $timestamp->format(DATE_ATOM)));
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
    
}
