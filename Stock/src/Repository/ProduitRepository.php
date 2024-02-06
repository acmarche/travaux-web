<?php

namespace AcMarche\Stock\Repository;

use AcMarche\Stock\Entity\Produit;
use AcMarche\Travaux\Doctrine\OrmCrudTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Produit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Produit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Produit[]    findAll()
 * @method Produit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitRepository extends ServiceEntityRepository
{
    use OrmCrudTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    public function getAll()
    {
        return $this->createQueryBuilder('produit')
            ->leftJoin('produit.categorie', 'categorie', 'WITH')
            ->addSelect('categorie')
            ->orderBy('produit.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Produit[]
     */
    public function search(array $args): array
    {
        $nom = $args['nom'] ?? null;
        $categorie = $args['categorie'] ?? null;

        $qb = $this->createQueryBuilder('produit');

        if ($nom) {
            $qb->andWhere('produit.nom LIKE :mot OR produit.description LIKE :mot ')
                ->setParameter('mot', '%'.$nom.'%');
        }

        if ($categorie) {
            $qb->andWhere('produit.categorie = :categorie')
                ->setParameter('categorie', $categorie);
        }

        return $qb
            ->orderBy('produit.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Produit[] Returns an array of Produit objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Produit
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
