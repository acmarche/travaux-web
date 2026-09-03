<?php

namespace AcMarche\Travaux\Repository;

use AcMarche\Travaux\Entity\UserModel;
use LdapRecord\Configuration\DomainConfiguration;
use LdapRecord\Connection;
use LdapRecord\Container;
use LdapRecord\ContainerException;
use LdapRecord\LdapRecordException;
use LdapRecord\Models\Model;
use LdapRecord\Query\Collection;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class LdapRepository
{
    public Connection $connection;

    public function __construct(
        #[Autowire(env: 'LDAP_STAFF_URL'), \SensitiveParameter]
        private readonly string $host,
        #[Autowire(env: 'LDAP_STAFF_BASE'), \SensitiveParameter]
        private readonly string $dn,
        #[Autowire(env: 'LDAP_STAFF_ADMIN'), \SensitiveParameter]
        private readonly string $user,
        #[Autowire(env: 'LDAP_STAFF_PWD'), \SensitiveParameter]
        private readonly string $password,
        private readonly LoggerInterface $logger,
    ) {

        $domain = new DomainConfiguration([
            'hosts' => [$this->host],
            'base_dn' => $this->dn,
            'username' => $this->user,
            'password' => $this->password,
            'port' => 636,
            'protocol' => 'ldaps://',
            'use_tls' => true,
            'use_sasl' => false,
            'version' => 3,
            'timeout' => 5,
            'follow_referrals' => false,
        ]);

        $this->connection = new Connection($domain);
    }

    /**
     * @throws LdapRecordException
     */
    public function connect(): void
    {
        $connection = $this->connection;
        if (!$this->connection->isConnected()) {
            if (!Container::hasConnection('employe')) {
                Container::addConnection($connection, 'employe');
            }
            try {
                $connection->connect($this->user, $this->password);
            } catch (\Exception|ContainerException $exception) {
                $this->logger->error('Connexion ldap impossible: '.$exception->getMessage(), [
                    'host' => $this->host,
                    'exception' => $exception,
                ]);

                throw new LdapRecordException(
                    'Connexion ldap impossible: '.$exception->getMessage(),
                    (int)$exception->getCode(),
                    $exception,
                );
            }
        }
    }

    /**
     * @return array|Collection|Model[]
     */
    public function getAll(): array|Collection
    {
        $this->connect();

        return UserModel::query()
            ->setBaseDn($this->dn)->get();
    }

    public function getEntry(string $uid): ?Model
    {
        $this->connect();
        $filter = "(&(|(sAMAccountName=$uid))(objectClass=person))";

        return UserModel::query()->findBy('sAMAccountName', $uid);
    }

    public function checkExist(string $nom): array|Collection
    {
        $this->connect();

        $filter = "(&(|(cn=$nom)(sAMAccountName=$nom)(mail=$nom)(proxyAddresses=$nom)(otherMailBoxes=$nom)(sAMAccountName=$nom))(objectClass=person))";

        return UserModel::query()
            ->orwhere('gosaMailAlternateAddress', '=', $nom)
            ->orWhere('mail', '=', $nom)
            ->orWhere('uid', '=', $nom)
            ->get();

    }

    /**
     * @param string $nom
     * @return Collection|array|UserModel[]
     */
    public function search(string $nom): Collection|array
    {
        $this->connect();
        $filter = "(&(|(cn=*$nom*)(sAMAccountName=*$nom*)(mail=*$nom*)(proxyAddresses=*$nom*)(otherMailBoxes=*$nom*)(sAMAccountName=*$nom*))(objectClass=person))";

        return UserModel::query()
            ->orWhere('sAMAccountName', 'contains', $nom)
            ->orWhere('mail', 'contains', $nom)
            ->orWhere('proxyAddresses', 'contains', $nom)
            ->get();
    }

    /**
     * @param Model $model
     * @param string $mail
     * @return void
     * @throws LdapRecordException
     * @throws \LdapRecord\Models\ModelDoesNotExistException
     */
    public function updateEmail(Model $model, string $mail): void
    {
        $model->setAttribute('mail', $mail);
        $this->connect();
        $model->update();
    }

    public function getEmail(?string $username): ?string
    {
        if (!$username) {
            return null;
        }
        $entry = $this->getEntry($username);
        if ($entry instanceof Model) {
            $emails = $entry->getAttribute('mail');
            if (is_array($emails) && $emails !== []) {
                return $emails[0];
            }
        }

        return null;
    }

    /**
     * @return string[]
     */
    public function getEntries(): array
    {
        $all = $this->getAll();
        $entries = [];
        foreach ($all as $entry) {
            $nom = '';
            if ($sn = $entry->getFirstAttribute('sn')) {
                $nom .= mb_strtoupper((string)$sn).' ';
            }
            if ($givenName = $entry->getFirstAttribute('givenName')) {
                $nom .= mb_strtoupper((string)$givenName).' ';
            }

            $entries[$entry->getFirstAttribute('sAMAccountName')] = $nom;
        }

        asort($entries);

        return $entries;
    }
}
