<?php

namespace AcMarche\Travaux\Repository;

use AcMarche\Travaux\Entity\Horaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method Horaire|null find($id, $lockMode = null, $lockVersion = null)
 * @method Horaire|null findOneBy(array $criteria, array $orderBy = null)
 * @method Horaire[]    findAll()
 * @method Horaire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HoraireRepository extends ServiceEntityRepository
{
    use OrmCrudTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Horaire::class);
    }

    /**
     * @return Horaire[]
     */
    public function findAllOrdered(): array
    {
        return $this->findBy(array(), array('position' => 'ASC'));
    }

    public function getQblForList(): QueryBuilder
    {
        return $this->createQueryBuilder('horaire')
            ->addOrderBy('horaire.nom');
    }

}
