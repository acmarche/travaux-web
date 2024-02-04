<?php

namespace AcMarche\Travaux\Repository;

use AcMarche\Travaux\Entity\Priorite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Priorite|null find($id, $lockMode = null, $lockVersion = null)
 * @method Priorite|null findOneBy(array $criteria, array $orderBy = null)
 * @method Priorite[]    findAll()
 * @method Priorite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrioriteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Priorite::class);
    }

    public function getForList(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('priorite');
        $qb->orderBy('priorite.intitule', 'DESC');//desc : normal at first

        return $qb;
    }

    public function getNormalForList(): QueryBuilder
    {
        return $this->createQueryBuilder('priorite')
            ->andWhere('priorite.intitule LIKE :titre')
            ->setParameter('titre', "Normal");
    }


    /**
     * Pour formulaire avec liste deroulante
     */
    public function getForSearch(): array
    {
        $qb = $this->createQueryBuilder('priorite');

        $qb->orderBy('priorite.intitule');
        $query = $qb->getQuery();

        $results = $query->getResult();
        $priorites = array();

        foreach ($results as $priorite) {
            $priorites[$priorite->getIntitule()] = $priorite->getId();
        }

        return $priorites;
    }
}
