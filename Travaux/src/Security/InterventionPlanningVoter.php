<?php

namespace AcMarche\Travaux\Security;

use AcMarche\Travaux\Entity\InterventionPlanning;
use AcMarche\Travaux\Entity\Security\User;
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
class InterventionPlanningVoter extends Voter
{
    // Defining these constants is overkill for this simple application, but for real
    // applications, it's a recommended practice to avoid relying on "magic strings"

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
        return $subject instanceof InterventionPlanning && in_array(
                $attribute,
                [self::SHOW, self::EDIT, self::DELETE],
                true
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $intervention, TokenInterface $token):bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if ($user->hasRole('ROLE_TRAVAUX_ADMIN')) {
            return true;
        }

        return match ($attribute) {
            self::SHOW => $this->canView($intervention, $token),
            self::EDIT => $this->canEdit($intervention, $token),
            self::DELETE => $this->canDelete($intervention, $token),
            default => false,
        };
    }

    private function canView(InterventionPlanning $intervention, TokenInterface $token): bool
    {
        if ($this->canEdit($intervention, $token)) {
            return true;
        }

        if (!$this->decisionManager->decide($token, ['ROLE_TRAVAUX_PLANNING'])) {
            return false;
        }

        return $this->checkOwner($intervention, $token);
    }

    private function canEdit(InterventionPlanning $intervention, TokenInterface $token): bool
    {
        return $this->checkOwner($intervention, $token);
    }

    private function canDelete(InterventionPlanning $intervention, TokenInterface $token): bool
    {
        return (bool) $this->canEdit($intervention, $token);
    }

    private function checkOwner(InterventionPlanning $intervention, TokenInterface $token): bool
    {
        $user = $token->getUser();
        return $intervention->user_add == $user->getUserIdentifier();
    }
}
