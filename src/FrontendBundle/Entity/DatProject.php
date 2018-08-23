<?php

namespace FrontendBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FrontendBundle\Entity\DatApi;
use FrontendBundle\Entity\DatApiProject;
use FrontendBundle\Entity\DatUrl;
use UserBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DatProject
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="FrontendBundle\Entity\DatProjectRepository")
 */
class DatProject
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="DatApiProject", mappedBy="project", cascade={"all"})
     */
    protected $apisprojects;

    /**
     * @ORM\OneToMany(targetEntity="DatUrl", mappedBy="project", cascade={"all"})
     */
    protected $urls;

    /**
     * @ORM\ManyToOne(targetEntity="NomApplicationType", inversedBy="projects")
     * @ORM\JoinColumn(name="nomapplicationtype_id", referencedColumnName="id", nullable=false)
     */
    protected $applicationtype;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="projects")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank()
     */
    protected $user;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->apisprojects = new ArrayCollection();
        $this->urls = new ArrayCollection();
    }

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
     * Set name
     *
     * @param string $name
     * @return DatProject
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return DatProject
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getApplicationtype()
    {
        return $this->applicationtype;
    }

    /**
     * @param mixed $applicationtype
     */
    public function setApplicationtype($applicationtype)
    {
        $this->applicationtype = $applicationtype;
    }

    /**
     * Add apisprojects
     *
     * @param DatApiProject $apisprojects
     * @return DatProject
     */
    public function addApisproject(DatApiProject $apisprojects)
    {
        $this->apisprojects[] = $apisprojects;

        return $this;
    }

    /**
     * Remove apisprojects
     *
     * @param DatApiProject $apisprojects
     */
    public function removeApisproject(DatApiProject $apisprojects)
    {
        $this->apisprojects->removeElement($apisprojects);
    }

    /**
     * Get apisprojects
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getApisprojects()
    {
        return $this->apisprojects;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return DatProject
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add urls
     *
     * @param DatUrl $urls
     * @return DatProject
     */
    public function addUrl(DatUrl $urls)
    {
        $this->urls[] = $urls;

        return $this;
    }

    /**
     * Remove urls
     *
     * @param DatUrl $urls
     */
    public function removeUrl(DatUrl $urls)
    {
        $this->urls->removeElement($urls);
    }

    /**
     * Get urls
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUrls()
    {
        return $this->urls;
    }
}
