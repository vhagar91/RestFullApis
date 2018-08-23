<?php

namespace FrontendBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * DatApiRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DatApiRepository extends EntityRepository
{
    public function save(DatApi $api)
    {
        $this->_em->persist($api);
        $this->_em->flush();
    }

    public function findById($id, array $orderBy = array())
    {
        if (empty($orderBy)) {
            $orderBy = ['name' => 'DESC'];
        }

        return $this->findBy(['id' => $id], $orderBy);
    }

    public function delete($api){
        $this->_em->remove($api);
        $this->_em->flush();
    }
}
