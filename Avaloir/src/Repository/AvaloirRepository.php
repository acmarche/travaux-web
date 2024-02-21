<?php

namespace AcMarche\Avaloir\Repository;

use AcMarche\Avaloir\Entity\Avaloir;
use AcMarche\Avaloir\Entity\Rue;
use AcMarche\Travaux\Doctrine\OrmCrudTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Avaloir|null find($id, $lockMode = null, $lockVersion = null)
 * @method Avaloir|null findOneBy(array $criteria, array $orderBy = null)
 * method Avaloir[]    findAll()
 * @method Avaloir[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AvaloirRepository extends ServiceEntityRepository
{
    use OrmCrudTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avaloir::class);
    }

    /**
     * @return Avaloir[]
     */
    public function findAll(): array
    {
        return $this->findBy(array(), array('id' => 'DESC'));
    }

    /**
     * @return Avaloir[]
     */
    public function findAllNotFinished(): array
    {
        return $this->createQueryBuilder('avaloir')
            ->andWhere('avaloir.avaloir.finish = :finish')
            ->setParameter('finish', false)
            ->addOrderBy('avaloir.createdAt', 'DESC')
            ->getQuery()->getResult();

    }

    /**
     * @return Avaloir[]
     */
    public function findLast(int $max = 300): array
    {
        return $this->createQueryBuilder('avaloir')
            ->leftJoin('avaloir.dates', 'dates', 'WITH')
            ->leftJoin('avaloir.commentaires', 'commentaires', 'WITH')
            ->addSelect('dates', 'commentaires')
            ->setMaxResults($max)
            ->addOrderBy('avaloir.createdAt', 'DESC')
            ->getQuery()->getResult();
    }

    /**
     * @param array $args
     * @return Avaloir[]
     */
    public function search(array $args): array
    {
        $qb = $this->setCriteria($args);

        return $qb->getQuery()->getResult();
    }

    public function setCriteria(array $args): QueryBuilder
    {
        $nom = $args['nom'] ?? null;
        $village = $args['village'] ?? null;
        $rue = $args['rue'] ?? null;
        $id = $args['id'] ?? 0;
        $date_debut = $args['date_debut'] ?? null;
        $date_fin = $args['date_fin'] ?? null;
        $quartier = $args['quartier'] ?? null;

        $qb = $this->createQueryBuilder('avaloir');
        $qb->leftJoin('avaloir.dates', 'dates', 'WITH');
        $qb->leftJoin('avaloir.commentaires', 'commentaires', 'WITH');
        $qb->addSelect('dates', 'commentaires');

        if ($nom) {
            $qb->andWhere('avaloir.descriptif LIKE :mot ')
                ->setParameter('mot', '%'.$nom.'%');
        }

        if ($rue) {
            $qb->andWhere('avaloir.rue LIKE :rue')
                ->setParameter('rue', '%'.$rue.'%');
        }

        if ($village) {
            $qb->andWhere('avaloir.localite = :village')
                ->setParameter('village', $village);
        }

        if ($quartier) {
            $qb->andWhere('quartier.id = :quartier')
                ->setParameter('quartier', $quartier);
        }

        if ($date_debut != null) {
            $date_start = $date_debut->format('Y-m-d');

            $date_end = $date_fin != null ? $date_fin->format('Y-m-d') : $date_start;

            $qb->andWhere('dates.jour BETWEEN :date_start AND :date_end')
                ->setParameter('date_start', $date_start)
                ->setParameter('date_end', $date_end);
        }

        if ($id) {
            $qb->andWhere("avaloir.id IN ('$id')");
        }

        $qb->addOrderBy('avaloir.createdAt', 'DESC');

        return $qb;
    }

    /**
     * @return Avaloir[]
     */
    public function getByVillage(?string $village): array
    {
        $qb = $this->createQueryBuilder('avaloir');
        $qb->andWhere('avaloir.localite = :village')
            ->setParameter('village', $village);

        return $qb->addOrderBy('avaloir.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

    }

    /**
     * @return Avaloir[]
     */
    public function findWithOutStreet(): array
    {
        return $this->createQueryBuilder('avaloir')
            ->andWhere('avaloir.rue IS NULL')
            ->addOrderBy('avaloir.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Avaloir[]
     */
    public function getByRue(Rue $rue): array
    {
        return $this->createQueryBuilder('avaloir')
            ->andWhere('avaloir.rue LIKE :rue')
            ->setParameter('rue', '%'.$rue->getNom().'%')
            ->addOrderBy('avaloir.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Avaloir[]
     */
    public function getAll(int $max = 5000): array
    {
        return $this->createQueryBuilder('avaloir')
            ->leftJoin('avaloir.dates', 'dates', 'WITH')
            ->leftJoin('avaloir.commentaires', 'commentaires', 'WITH')
            ->addSelect('dates', 'commentaires')
            ->addOrderBy('avaloir.createdAt', 'DESC')
            ->setMaxResults($max)
            ->getQuery()
            ->getResult();
    }

    public function getLastUpdatedAvaloir(): ?Avaloir
    {
        return $this->createQueryBuilder('avaloir')
            ->addOrderBy('avaloir.updatedAt', 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    /**
     * @param \DateTime $date
     * @return array|Avaloir[]
     */
    public function findByDate(\DateTime $date): array
    {
        return $this->createQueryBuilder('avaloir')
            ->leftJoin('avaloir.dates', 'dates', 'WITH')
            ->leftJoin('avaloir.commentaires', 'commentaires', 'WITH')
            ->addSelect('dates', 'commentaires')
            ->andWhere('avaloir.createdAt LIKE :date')
            ->setParameter('date', $date->format('Y-m-d').'%')
            ->getQuery()
            ->getResult();
    }
}
