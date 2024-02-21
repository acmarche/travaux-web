<?php

namespace AcMarche\Avaloir\Repository;

use AcMarche\Avaloir\Entity\DateNettoyage;
use AcMarche\Travaux\Doctrine\OrmCrudTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DateNettoyage|null find($id, $lockMode = null, $lockVersion = null)
 * @method DateNettoyage|null findOneBy(array $criteria, array $orderBy = null)
 * @method DateNettoyage[]    findAll()
 * @method DateNettoyage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DateNettoyageRepository extends ServiceEntityRepository
{
    use OrmCrudTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DateNettoyage::class);
    }

    public function findForNew()
    {
        $qb = $this->createQueryBuilder('date');

        $qb->andWhere('date.avaloir IS NOT NULL');
        $query = $qb->getQuery();

        return $query->getResult();
    }
}
