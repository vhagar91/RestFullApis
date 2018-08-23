<?php

namespace FrontendBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * NomApplicationTypeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class NomApplicationTypeRepository extends EntityRepository
{
    public function save(NomApplicationType $nomApplicationType)
    {
        $this->_em->persist($nomApplicationType);
        $this->_em->flush();
    }

    public function findById($id, array $orderBy = array())
    {
        if (empty($orderBy)) {
            $orderBy = ['name' => 'DESC'];
        }

        return $this->findBy(['id' => $id], $orderBy);
    }
}
