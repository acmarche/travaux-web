<?php
/**
 * Created by PhpStorm.
 * User: jfsenechal
 * Date: 8/12/16
 * Time: 13:25
 */

namespace AcMarche\Travaux\Event;

use AcMarche\Travaux\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Workflow\Event\Event;

/**
 * @TODO Comment l'appeler depuis un controller !!
 * http://symfony.com/doc/current/workflow/usage.html#using-events
 */
class WorkflowSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private AuthorizationCheckerInterface $authorizationChecker,
        private Mailer $mailer
    ) {
    }

    /**
     * @override
     */
    public static function getSubscribedEvents(): array
    {
        return array(
            'workflow.intervention_publication.enter.publish' => array('publish'),
        );
    }

    public function publish(Event $event): void
    {
        $intervention = $event->getSubject();
    }
}
