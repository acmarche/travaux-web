<?php

namespace AcMarche\Travaux\Security;

use AcMarche\Travaux\Entity\Security\User;
use AcMarche\Travaux\Entity\Suivi;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * It grants or denies permissions for actions related to blog posts (such as
 * showing, editing and deleting posts).
 *
 * See http://symfony.com/doc/current/security/voters.html
 *
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 */
class SuiviVoter extends Voter
{
    // Defining these constants is overkill for this simple application, but for real
    // applications, it's a recommended practice to avoid relying on "magic strings"

    public const ADD = 'add';
    public const SHOW = 'show';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    public function __construct(protected AccessDecisionManagerInterface $decisionManager)
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject):bool
    {
        // this voter is only executed for three specific permissions on Post objects
        return $subject instanceof Suivi && in_array(
                $attribute,
                [self::ADD, self::SHOW, self::EDIT, self::DELETE],
                true
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $suivi, TokenInterface $token):bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if ($user->hasRole('ROLE_TRAVAUX_ADMIN')) {
            return true;
        }
        return match ($attribute) {
            self::SHOW => $this->canView($suivi, $token),
            self::ADD => $this->canAddSuivi($suivi, $token),
            self::EDIT => $this->canEdit($suivi, $token),
            self::DELETE => $this->canDelete($suivi, $token),
            default => false,
        };
    }

    private function canView(Suivi $suivi, TokenInterface $token): bool
    {
        if ($this->canEdit($suivi, $token)) {
            return true;
        }

        if ($this->decisionManager->decide($token, ['ROLE_TRAVAUX_REDACTEUR'])) {
            return true;
        }

        if ($this->decisionManager->decide($token, ['ROLE_TRAVAUX_AUTEUR'])) {
            return true;
        }

        if ($this->decisionManager->decide($token, ['ROLE_TRAVAUX_CONTRIBUTEUR'])) {
            return $this->checkOwner($suivi, $token);
        }

        return false;
    }

    private function canEdit(Suivi $suivi, TokenInterface $token): bool
    {
        return $this->checkOwner($suivi, $token);
    }

    private function canAdd(Suivi $suivi, TokenInterface $token): bool
    {
        return (bool) $this->decisionManager->decide($token, ['ROLE_TRAVAUX_ADD']);
    }

    private function canAddSuivi(Suivi $suivi, TokenInterface $token): bool
    {
        if ($this->canEdit($suivi, $token)) {
            return true;
        }

        if ($this->decisionManager->decide($token, [''])) {
            return $this->checkOwner($suivi, $token);
        }

        return false;
    }

    private function canDelete(Suivi $suivi, TokenInterface $token): bool
    {
        return (bool) $this->canEdit($suivi, $token);
    }

    private function checkOwner(Suivi $suivi, TokenInterface $token): bool
    {
        $user = $token->getUser();
        return $suivi->getUserAdd() == $user->getUserIdentifier();
    }
}
