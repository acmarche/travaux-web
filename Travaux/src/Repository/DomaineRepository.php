<?php

namespace AcMarche\Travaux\Repository;

use AcMarche\Travaux\Entity\Domaine;
use Doctrine\ORM\EntityRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Domaine|null find($id, $lockMode = null, $lockVersion = null)
 * @method Domaine|null findOneBy(array $criteria, array $orderBy = null)
 * method Domaine[]    findAll()
 * @method Domaine[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DomaineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Domaine::class);
    }
    /**
     * @return Domaine[]
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
        $qb = $this->createQueryBuilder('d');

        $qb->orderBy('d.intitule');
        $query = $qb->getQuery();

        $results = $query->getResult();
        $domaines = array();

        foreach ($results as $domaine) {
            $domaines[$domaine->getIntitule()] = $domaine->getId();
        }

        return $domaines;
    }
}
