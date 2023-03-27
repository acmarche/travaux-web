<?php

namespace AcMarche\Travaux\Repository;

use AcMarche\Travaux\Entity\Employe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method Employe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Employe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Employe[]    findAll()
 * @method Employe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmployeRepository extends ServiceEntityRepository
{
    use OrmCrudTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employe::class);
    }

    /**
     * @return Employe[]
     */
    public function findAllOrdered(): array
    {
        return $this->findBy(array(), array('nom' => 'ASC'));
    }

    public function searchByName(string $name): array
    {
        return $this->createQueryBuilder('employe')
            ->andWhere('employe.nom LIKE :name OR employe.prenom LIKE :name')
            ->setParameter('name', $name)
            ->orderBy('b.intitule')
            ->getQuery()
            ->getResult();

    }
}
