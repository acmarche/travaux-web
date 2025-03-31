<?php

namespace AcMarche\Travaux\Repository;

use AcMarche\Travaux\Doctrine\OrmCrudTrait;
use AcMarche\Travaux\Entity\Intervention;
use AcMarche\Travaux\Entity\Security\Group;
use AcMarche\Travaux\Entity\Security\User;
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
    public function setCriteria(array $args, bool $cloture = false): QueryBuilder
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
        $archive = $args['archive'] ?? null;
        $withAValider = $args['withAValider'] ?? false;
        $affectation = $args['affectation'] ?? 0;
        $place = $args['place'] ?? null;
        $sort = $args['sort'] ?? null;
        $affecte_prive = $args['affecte_prive'] ?? false;
        $date_execution = $args['date_execution'] ?? false;

        $qb = $this->createQbl();

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


        if ($archive) {
            if ($archive == 1) {
                $qb->andWhere("intervention.archive = 1 ");
            }
            //$archive == 2
            //pas de contrainte;
        } else {
            $qb->andWhere("intervention.archive = 0 ");
        }

        if ($etat) {
            $qb->andWhere('intervention.etat = :etat')
                ->setParameter('etat', $etat);
        } else {
            if ($cloture) {
                $qb->andWhere('intervention.etat != :etat')
                    ->setParameter('etat', 4);//pas les clotures
            }
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
        } else {
            $qb->andWhere("intervention.affecte_prive = :prive")
                ->setParameter('prive', 0);
        }

        if ($place) {
            if (!is_array($place)) {
                $qb->andWhere('intervention.currentPlace LIKE :place')
                    ->setParameter('place', '%'.$place.'%');
            } else {
                foreach ($place as $value) {
                    $qb->orWhere('intervention.currentPlace LIKE :arg')
                        ->setParameter('arg', '%'.$value.'%');
                }
            }
        } /**
         * intervention en attente de validation
         * non pour tous
         * sauf pour les contributeurs sinon ne voit rien
         */
        elseif (!$withAValider) {
            $qb->andWhere('intervention.currentPlace LIKE :place')
                ->setParameter('place', '%published%');
        }

        $today = new DateTime('now');
        $qb->andWhere('intervention.date_execution <= :date OR intervention.date_execution IS NULL')
            ->setParameter('date', $today->format('Y-m-d'));

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
     */
    public function search(array $args, bool $cloture)
    {
        $currentUser = $args['current_user'] ?? null;
        $role = $args['role'] ?? null;

        $qb = $this->setCriteria($args, $cloture);

        if ($currentUser && $role) {
            $this->setUserConstraint($currentUser, $role, $qb);
        }

        $query = $qb->getQuery();

        return $query->getResult();
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

    public function setUserConstraint(User $user, $role, QueryBuilder $qb): QueryBuilder
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
