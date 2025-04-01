<?php

namespace AcMarche\Travaux\Service;

enum WorkflowEnum: string
{
    case AUTEUR_CHECKING = 'auteur_checking';
    case REDACTEUR = 'redacteur';
    case ADMIN_CHECKING = 'admin_checking';
    case DELETED = 'deleted';
    case PUBLISHED = 'published';

    public function getLabel(): string
    {
        return match ($this) {
            self::AUTEUR_CHECKING => 'En attente de validation par un auteur',
            self::REDACTEUR => 'Disponible pour les rédacteurs',
            self::ADMIN_CHECKING => 'En attente de validation par un administrateur',
            self::DELETED => 'Refusé',
            self::PUBLISHED => 'Validé',
        };
    }

}
