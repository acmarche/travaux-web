<?php

namespace AcMarche\Travaux\Repository;

use Doctrine\ORM\QueryBuilder;
use AcMarche\Travaux\Entity\Etat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Etat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Etat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Etat[]    findAll()
 * @method Etat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EtatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Etat::class);
    }
    public function getForList(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('etat');
        $qb->orderBy('etat.intitule', 'DESC');
        return $qb;
    }

    public function getForListDefault(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('etat');
        $qb->andWhere('etat.intitule LIKE :titre')
            ->setParameter('titre', "Nouveau");

        return $qb;
    }

    /**
     * Pour formulaire avec liste deroulante
     */
    public function getForSearch(): array
    {
        $qb = $this->createQueryBuilder('etat');

        $qb->orderBy('etat.intitule');
        $query = $qb->getQuery();

        $results = $query->getResult();
        $etats = array();

        foreach ($results as $etat) {
            $etats[$etat->getIntitule()] = $etat->getId();
        }

        return $etats;
    }
}
