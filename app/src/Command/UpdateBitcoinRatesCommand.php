<?php

namespace App\Command;

use App\Entity\BitcoinRates;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(name: 'app:update-rate')]
class UpdateBitcoinRatesCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private HttpClientInterface $httpClient;
    private string $apiUrl;
    private bool $running = true;
    private array $currencies = ['usd', 'eur', 'uah'];

    public function __construct(EntityManagerInterface $entityManager, HttpClientInterface $httpClient, string $apiUrl)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->httpClient = $httpClient;
        $this->apiUrl = $apiUrl;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        declare(ticks = 1);
        pcntl_signal(SIGTERM, function () {
            $this->running = false;
        });

        $intervalSeconds = 300;
        $output->writeln('Starting Bitcoin rate updater...');

        while ($this->running) {
            $startTime = microtime(true);

            try {
                $response = $this->httpClient->request('GET', $this->apiUrl);
                $data = $response->toArray();

                if (!isset($data['bitcoin'])) {
                    $output->writeln('Failed to fetch rates.');
                } else {
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
                }
            } catch (\Exception $e) {
                $output->writeln('Error: ' . $e->getMessage());
            }

            $elapsed = microtime(true) - $startTime;
            $sleepTime = max(0, $intervalSeconds - $elapsed);
            usleep((int)($sleepTime * 1_000_000));
        }

        $output->writeln('Bitcoin rate updater stopped.');
        return Command::SUCCESS;
    }
}
