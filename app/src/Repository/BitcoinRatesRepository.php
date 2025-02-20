<?php

namespace App\Repository;

use App\Entity\BitcoinRates;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BitcoinRatesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BitcoinRates::class);
    }

    public function findLatestRates(array $currencies): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.currency IN (:currencies)')
            ->setParameter('currencies', $currencies)
            ->orderBy('r.timestamp', 'DESC')
            ->setMaxResults(count($currencies))
            ->getQuery()
            ->getResult();
    }
    

    public function findHistory(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.timestamp BETWEEN :from AND :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->orderBy('r.timestamp', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
