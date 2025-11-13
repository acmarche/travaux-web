<?php

namespace AcMarche\Travaux\Command;

use AcMarche\Travaux\Entity\Security\User;
use AcMarche\Travaux\Repository\LdapRepository;
use AcMarche\Travaux\Repository\UserRepository;
use LdapRecord\Models\Model;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'apptravaux:synchro',
    description: 'Synchronise les utilisateurs',
)]
class SyncCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly LdapRepository $ldapRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach ($this->userRepository->findAll() as $user) {
            $userModel = $this->ldapRepository->getEntry($user->getUsername());
            if (!$userModel instanceof Model) {
                $io->writeln("Remove user from travaux ".$user->getUsername());
                $this->userRepository->remove($user);
            } else {
                if (!$this->isEnable($userModel)) {
                    $io->writeln("Remove user from travaux ".$user->getUsername());
                    $this->userRepository->remove($user);
                } else {
                    $this->updateUser($userModel, $user);
                }
            }
        }

        $this->userRepository->flush();

        return Command::SUCCESS;
    }

    protected function updateUser(Model $model, User $user): ?User
    {
        $username = $model->getFirstAttribute('sAMAccountName');

        $nom = $model->getFirstAttribute('sn');
        $prenom = $model->getFirstAttribute('givenName');
        $email = $model->getFirstAttribute('mail');

        $user->setNom($nom);
        $user->setPrenom($prenom);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $user->setEmail($email);
        }

        return $user;
    }

    public function isEnable(Model $entry): bool
    {
        $userAccountControl = (int)$entry->getFirstAttribute('userAccountControl');

        if ($userAccountControl === 512) {
            return true;
        }

        return false;

    }
}
