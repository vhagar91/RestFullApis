<?php

namespace FrontendBundle\Entity;

use Doctrine\ORM\EntityRepository;
use UserBundle\Entity\User;

/**
 * DatProjectRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DatProjectRepository extends EntityRepository
{
    public function save(DatProject $project)
    {
        $this->_em->persist($project);
        $this->_em->flush();
    }

    public function findByUser(User $user, array $orderBy = array())
    {
        if (empty($orderBy)) {
            $orderBy = ['name' => 'DESC'];
        }

        return $this->findBy(['user' => $user], $orderBy);
    }

    public function findById($id, array $orderBy = array())
    {
        if (empty($orderBy)) {
            $orderBy = ['name' => 'DESC'];
        }

        return $this->findBy(['id' => $id], $orderBy);
    }

    public function deleteProject($project){
        $this->_em->remove($project);
        $this->_em->flush();
    }
}