<?php

namespace App\Repository;

use App\Entity\WorkDayRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<WorkDayRecord>
 */
class WorkDayRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkDayRecord::class);
    }

    public function findByEmployeeIdAndDateRange(Uuid $employeeId, \DateTime $startDate, \DateTime $endDate): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('IDENTITY(r.employee) = :employeeId')
            ->andWhere('r.workingDayDate BETWEEN :start AND :end')
            ->setParameter('employeeId', $employeeId, 'uuid')
            ->setParameter('start', $startDate, 'datetime')
            ->setParameter('end', $endDate, 'datetime')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return WorkDayRecord[] Returns an array of WorkDayRecord objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('w.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?WorkDayRecord
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
