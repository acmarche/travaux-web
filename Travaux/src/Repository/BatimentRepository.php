<?php

namespace AcMarche\Travaux\Repository;

use AcMarche\Travaux\Entity\Batiment;
use Doctrine\ORM\EntityRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method Batiment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Batiment|null findOneBy(array $criteria, array $orderBy = null)
 * method Batiment[]    findAll()
 * @method Batiment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BatimentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Batiment::class);
    }

    /**
     * @return Batiment[]
     */
    public function findAll(): array
    {
        return $this->findBy(array(), array('intitule' => 'ASC'));
    }

    /**
     * Pour formulaire avec liste deroulante
     */
    public function getForSearch(): array
    {
        $qb = $this->createQueryBuilder('b');

        $qb->orderBy('b.intitule');
        $query = $qb->getQuery();

        $results = $query->getResult();
        $batiments = array();

        foreach ($results as $batiment) {
            $batiments[$batiment->getIntitule()] = $batiment->getId();
        }

        return $batiments;
    }
}
