<?php

namespace FrontendBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * DatApiProjectRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DatApiProjectRepository extends EntityRepository
{
    public function findByApiKey($apikey){
        return $this->findBy(['apikey' => $apikey]);
    }

    public function findByApi(DatApi $api)
    {
        return $this->findBy(['api' => $api]);
    }
}