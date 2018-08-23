<?php

namespace BackendBundle\Controller;


use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use FrontendBundle\Entity\DatProject;

class SearchIndexerSubscriber implements EventSubscriber
{

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
            Events::prePersist,
            Events::preUpdate,
            Events::preRemove
        );
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->postIndex($args);
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->postIndex($args);
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $this->postIndex($args);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->preIndex($args);
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $this->preIndex($args);
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $this->preIndex($args);
    }

    public function preIndex(LifecycleEventArgs $args){

        /*$dispatcher = new EventDispatcher();
        $dispatcher->dispatch('event_dispatcher');*/

        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        // tal vez sólo quieres actuar en alguna entidad "producto"
        if ($entity instanceof DatProject) {
            foreach ($entity as $key => $value) {
                $a = 1;
                $a = $a + 1;
            }
        }
    }

    public function postIndex(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        // tal vez sólo quieres actuar en alguna entidad "producto"
        if ($entity instanceof DatProject) {
            foreach ($entity as $key => $value) {
                $a = 1;
                $a = $a + 1;
            }
        }
    }
}