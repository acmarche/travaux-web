<?php

namespace AcMarche\Travaux\Repository;

use AcMarche\Travaux\Doctrine\OrmCrudTrait;
use AcMarche\Travaux\Entity\Intervention;
use AcMarche\Travaux\Entity\Security\Group;
use AcMarche\Travaux\Entity\Security\User;
use AcMarche\Travaux\Service\WorkflowEnum;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @method Intervention|null find($id, $lockMode = null, $lockVersion = null)
 * @method Intervention|null findOneBy(array $criteria, array $orderBy = null)
 * @method Intervention[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InterventionRepository extends ServiceEntityRepository
{
    use OrmCrudTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Intervention::class);
    }

    /**
     * @return Intervention[]
     */
    public function findAll(): array
    {
        return $this->findBy(array(), array('intitule' => 'ASC'));
    }

    /**
     * @param $args
     * @throws Exception
     */
    public function setCriteria(array $args): QueryBuilder
    {
        $intitule = $args['intitule'] ?? null;
        $username = $args['user'] ?? 0;
        $domaine = $args['domaine'] ?? 0;
        $batiment = $args['batiment'] ?? 0;
        $etat = $args['etat'] ?? null;
        $priorite = $args['priorite'] ?? null;
        $id = $args['id'] ?? 0;
        $numero = $args['numero'] ?? 0;
        $date_introduction = $args['date_introduction'] ?? null;
        $date_debut = $args['date_debut'] ?? null;
        $date_fin = $args['date_fin'] ?? null;
        //1 => archive, 2 => les deux, pas definis pas d'archive
        $archive = $args['archive'];
        $affectation = $args['affectation'] ?? 0;
        $placeSelected = $args['placeSelected'] ?? null;
        $sort = $args['sort'] ?? null;
        $affecte_prive = $args['affecte_prive'] ?? false;
        $category = $args['categorie'] ?? null;

        $qb = $this->createQbl();

        $qb->andWhere('intervention.affecte_prive != 1');

        if ($intitule) {
            $qb->andWhere(
                'intervention.intitule LIKE :mot OR intervention.descriptif LIKE :mot OR intervention.affectation LIKE :mot'
            )
                ->setParameter('mot', '%'.$intitule.'%');
        }

        if ($affectation) {
            $qb->andWhere('intervention.affectation LIKE :aff')
                ->setParameter('aff', '%'.$affectation.'%');
        }

        if ($archive !== null) {
            $qb->andWhere("intervention.archive = $archive ");
        }

        if ($etat) {
            $qb->andWhere('intervention.etat = :etat')
                ->setParameter('etat', $etat);
        }

        if ($date_introduction) {
            $date_introduction = $date_introduction->format('Y-m-d');
            $qb->andWhere('intervention.date_introduction = :date')
                ->setParameter('date', $date_introduction);
        }

        if ($date_debut) {
            $date_start = $date_debut->format('Y-m-d');

            $date_end = $date_fin ? $date_fin->format('Y-m-d') : $date_start;

            $qb->andWhere('intervention.date_introduction BETWEEN :date_start AND :date_end')
                ->setParameter('date_start', $date_start)
                ->setParameter('date_end', $date_end);
        }

        if ($batiment) {
            $qb->andWhere('batiment.id = :bat')
                ->setParameter('bat', $batiment);
        }

        if ($domaine) {
            $qb->andWhere('domaine.id = :dom')
                ->setParameter('dom', $domaine);
        }

        if ($username) {
            $qb->andWhere('intervention.user_add = :username')
                ->setParameter('username', $username);
        }

        if ($priorite) {
            $qb->andWhere('intervention.priorite = :priorite')
                ->setParameter('priorite', $priorite);
        }

        if ($id) {
            $qb->andWhere("intervention.id IN ('$id')");
        }

        if ($numero) {
            $qb->andWhere("intervention.id = :num ")
                ->setParameter('num', $numero);
        }

        if ($affecte_prive) {
            $qb->andWhere("intervention.affecte_prive = :prive")
                ->setParameter('prive', 1);
        }

        if ($placeSelected instanceof WorkflowEnum) {
            $qb->andWhere('intervention.currentPlace LIKE :place')
                ->setParameter('place', '%'.$placeSelected->value.'%');
        }

        if ($category) {
            $qb->andWhere('intervention.categorie = :cat')
                ->setParameter('cat', $category);
        }

        if ($sort) {
            $qb->addOrderBy('intervention.'.$sort, 'DESC');
        } else {
            $qb->addOrderBy('intervention.priorite', 'DESC');
            $qb->addOrderBy('intervention.date_introduction', 'ASC');
        }

        return $qb;
    }

    /**
     * @param array $args
     * @return Intervention[]
     * @throws Exception
     */
    public function search(array $args): array
    {
        $currentUser = $args['current_user'] ?? null;
        $role = $args['role'] ?? null;

        $qb = $this->setCriteria($args);

        if ($currentUser && $role) {
            $this->setUserConstraint($currentUser, $role, $qb);
        }

        $query = $qb->getQuery();

        return $query->getResult();
    }

    /**
     * @param int $categoryId
     * @return Intervention[]
     */
    public function findOlderThan(DateTime $dateTime): array
    {
        return $this->createQbl()
            ->andWhere('intervention.updatedAt < :cat ')
            ->setParameter('cat', $dateTime->format('Y-m-d'))
            ->getQuery()
            ->getResult();
    }

    /**
     * Interventions à faire plus tard
     * @return Intervention[]
     * @throws Exception
     */
    public function getInterventionsReportees(): array
    {
        $today = new DateTime('now');
        $qb = $this->createQbl();

        $qb->andWhere('intervention.date_execution > :date ')
            ->setParameter('date', $today->format('Y-m-d'));

        $qb->andWhere("intervention.archive = 0 ");

        $query = $qb->getQuery();

        return $query->getResult();
    }

    /**
     * Retourne La liste des interventions à valider pour l'admin ou l'auteur
     * @param array $places
     * @return Intervention[]
     */
    public function getInterventionsToValid(array $places = []): array
    {
        return $this->createQbl()
            ->andWhere("intervention.archive = 0 ")
            ->andWhere("intervention.currentPlace IN (:places) ")
            ->setParameter('places', $places)
            ->getQuery()
            ->getResult();
    }

    public function setUserConstraint(User $user, string $role, QueryBuilder $qb): QueryBuilder
    {
        $em = $this->getEntityManager();
        $usernames = false;

        /**
         * vois toutes ses demandes et celles du groupe contributeur
         */
        if ($role === 'AUTEUR') {
            $group = $em->getRepository(Group::class)->findOneBy(
                array(
                    'name' => 'TRAVAUX_CONTRIBUTEUR',
                )
            );

            if ($group !== null) {
                $usernames = [];
                $users = $group->getUsers();
                foreach ($users as $tuser) {
                    $usernames[] = $tuser->getUserIdentifier();
                }
                $usernames[] = $user->getUserIdentifier();
            }
            $qb->andWhere("intervention.currentPlace NOT LIKE '%auteur_checking%' ");
        }

        /**
         * ne peut voir que ses demandes
         */
        if ($role === 'CONTRIBUTEUR') {
            $usernames = $user->getUserIdentifier();
        }

        /**
         * voir les siennes
         * celles en attente de validation admin
         */
        if ($role === 'REDACTEUR') {
            $username = $user->getUserIdentifier();
            /**
             * WHERE ((`user_add` LIKE 'redacteur' AND `current_place` LIKE '%admin_checking%')
             * OR (`user_add` LIKE 'redacteur')
             * OR (`current_place` LIKE '%published%'))
             */

            $qb->andWhere(
                "(
            (intervention.user_add = :user AND intervention.currentPlace NOT LIKE '%admin_checking%') OR
            (intervention.user_add = :user) OR
            (intervention.currentPlace LIKE '%published%')
            )"
            )
                ->setParameter('user', $username);
        }

        if (is_array($usernames) && $usernames !== []) {
            $string = implode("','", $usernames);
            $qb->andWhere("intervention.user_add IN ('$string')");
        } elseif ($usernames) {
            $qb->andWhere('intervention.user_add = :user')
                ->setParameter('user', $usernames);
        }

        return $qb;
    }

    /**
     * @return array|Intervention[]
     */
    public function findByDates(DateTime $dateStart, DateTime $dateEnd): array
    {
        return $this->createQbl()
            ->andWhere('intervention.date_introduction BETWEEN :date_start AND :date_end')
            ->setParameter('date_start', $dateStart)
            ->setParameter('date_end', $dateEnd)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $args
     * @return Intervention[]
     */
    public function findPrive(bool $cloture): array
    {
        return $this->createQbl()->where('intervention.affecte_prive = 1')
            ->andWhere("intervention.archive = 0 ")->getQuery()->getResult();
    }

    private function createQbl(): QueryBuilder
    {
        return $this->createQueryBuilder('intervention')
            ->leftJoin('intervention.categorie', 'categorie', 'WITH')
            ->leftJoin('intervention.batiment', 'batiment', 'WITH')
            ->leftJoin('intervention.domaine', 'domaine', 'WITH')
            ->leftJoin('intervention.documents', 'documents', 'WITH')
            ->addSelect('categorie', 'batiment', 'domaine', 'documents');
    }
}
