<?php

namespace FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FrontendBundle\Entity\DatApi;
use FrontendBundle\Entity\DatProject;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DatApiProject
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="FrontendBundle\Entity\DatApiProjectRepository")
 */
class DatApiProject
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="DatApi", inversedBy="apisprojects")
     * @ORM\JoinColumn(name="api_id", referencedColumnName="id", nullable=false)
     */
    protected $api;

    /**
     * @ORM\ManyToOne(targetEntity="DatProject", inversedBy="apisprojects")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", nullable=false)
     */
    protected $project;

    /**
     * @var string
     *
     * @ORM\Column(name="apikey", type="string", nullable=false)
     * @Assert\NotBlank()
     */
    private $apikey;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set apikey
     *
     * @param string $apikey
     * @return DatApiProject
     */
    public function setApikey($apikey)
    {
        $this->apikey = $apikey;

        return $this;
    }

    /**
     * Get apikey
     *
     * @return string 
     */
    public function getApikey()
    {
        return $this->apikey;
    }

    /**
     * Set api
     *
     * @param DatApi $api
     * @return DatApiProject
     */
    public function setApi(DatApi $api = null)
    {
        $this->api = $api;

        return $this;
    }

    /**
     * Get api
     *
     * @return DatApi
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * Set project
     *
     * @param DatProject $project
     * @return DatApiProject
     */
    public function setProject(DatProject $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return DatProject
     */
    public function getProject()
    {
        return $this->project;
    }
}
