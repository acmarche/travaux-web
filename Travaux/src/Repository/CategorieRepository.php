<?php

namespace AcMarche\Travaux\Repository;

use AcMarche\Travaux\Entity\Categorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Categorie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Categorie|null findOneBy(array $criteria, array $orderBy = null)
 * method Categorie[]    findAll()
 * @method Categorie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategorieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categorie::class);
    }

    /**
     * @return Categorie[]
     */
    public function findAll(): array
    {
        return $this->findBy(array(), array('intitule' => 'ASC'));
    }

    public function getForList(): array
    {
        return $this->createQueryBuilder('categorie')
            ->orderBy('categorie.intitule', 'ASC')
            ->getQuery()->getResult();

    }

    public function getInterventionForList(): array
    {
        return $this->createQueryBuilder('categorie')
            ->andWhere('categorie.intitule LIKE :titre')
            ->setParameter('titre', "Intervention")
            ->getQuery()->getResult();
    }

    /**
     * Pour formulaire avec liste deroulante
     */
    public function getForSearch(): array
    {
        $qb = $this->createQueryBuilder('c');

        $qb->orderBy('c.intitule');
        $query = $qb->getQuery();

        $results = $query->getResult();
        $categories = array();

        foreach ($results as $categorie) {
            $categories[$categorie->getIntitule()] = $categorie->getId();
        }

        return $categories;
    }
}
