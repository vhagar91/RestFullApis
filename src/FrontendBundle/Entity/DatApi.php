<?php

namespace FrontendBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FrontendBundle\Entity\DatApiProject;
use FrontendBundle\Entity\DatProject;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DatApi
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="FrontendBundle\Entity\DatApiRepository")
 */
class DatApi
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
     * @var string
     *
     * @ORM\Column(name="class", type="string", length=100, nullable=false)
     * @Assert\NotBlank()
     */
    private $class;

    /**
     * @var string
     *
     * @ORM\Column(name="db_host", type="string", nullable=false)
     * @Assert\NotBlank()
     */
    private $db_host;

    /**
     * @var integer
     *
     * @ORM\Column(name="db_port", type="integer", nullable=false)
     * @Assert\NotBlank()
     */
    private $db_port;

    /**
     * @var string
     *
     * @ORM\Column(name="db_name", type="string", nullable=false)
     * @Assert\NotBlank()
     */
    private $db_name;

    /**
     * @var string
     *
     * @ORM\Column(name="db_user", type="string", nullable=false)
     * @Assert\NotBlank()
     */
    private $db_user;

    /**
     * @var string
     *
     * @ORM\Column(name="db_password", type="string", nullable=false)
     * @Assert\NotBlank()
     */
    private $db_password;

    /**
     * @ORM\ManyToOne(targetEntity="NomDriver", inversedBy="apis")
     * @ORM\JoinColumn(name="nomdriver_id", referencedColumnName="id", nullable=false)
     */
    protected $driver;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", nullable=false)
     * @Assert\NotBlank()
     */
    private $code;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=true)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="NomApiType", inversedBy="apis")
     * @ORM\JoinColumn(name="nomapitype_id", referencedColumnName="id", nullable=false)
     */
    protected $apitype;

    /**
     * @ORM\OneToMany(targetEntity="DatApiProject", mappedBy="api")
     */
    protected $apisprojects;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->apisprojects = new ArrayCollection();
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
     * @return DatApi
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
     * @return DatApi
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
     * Set db_host
     *
     * @param string $dbHost
     * @return DatApi
     */
    public function setDbHost($dbHost)
    {
        $this->db_host = $dbHost;

        return $this;
    }

    /**
     * Get db_host
     *
     * @return string 
     */
    public function getDbHost()
    {
        return $this->db_host;
    }

    /**
     * Get db_host
     *
     * @return string
     */
    public function getDb_host()
    {
        return $this->db_host;
    }

    /**
     * Set db_port
     *
     * @param integer $dbPort
     * @return DatApi
     */
    public function setDbPort($dbPort)
    {
        $this->db_port = $dbPort;

        return $this;
    }

    /**
     * Get db_port
     *
     * @return integer 
     */
    public function getDbPort()
    {
        return $this->db_port;
    }

    /**
     * Get db_port
     *
     * @return integer
     */
    public function getDb_port()
    {
        return $this->db_port;
    }

    /**
     * Set db_name
     *
     * @param string $dbName
     * @return DatApi
     */
    public function setDbName($dbName)
    {
        $this->db_name = $dbName;

        return $this;
    }

    /**
     * Get db_name
     *
     * @return string 
     */
    public function getDbName()
    {
        return $this->db_name;
    }

    /**
     * Get db_name
     *
     * @return string
     */
    public function getDb_name()
    {
        return $this->db_name;
    }

    /**
     * Set db_password
     *
     * @param string $dbPassword
     * @return DatApi
     */
    public function setDbPassword($dbPassword)
    {
        $this->db_password = $dbPassword;

        return $this;
    }

    /**
     * Get db_password
     *
     * @return string 
     */
    public function getDbPassword()
    {
        return $this->db_password;
    }

    /**
     * Get db_password
     *
     * @return string
     */
    public function getDb_password()
    {
        return $this->db_password;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return DatApi
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set status
     *
     * @param integer|boolean $status
     * @return DatApi
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Add apisprojects
     *
     * @param DatApiProject $apisprojects
     * @return DatApi
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
     * Set apitype
     *
     * @param \FrontendBundle\Entity\NomApiType $apitype
     * @return DatApi
     */
    public function setApitype(\FrontendBundle\Entity\NomApiType $apitype)
    {
        $this->apitype = $apitype;

        return $this;
    }

    /**
     * Get apitype
     *
     * @return \FrontendBundle\Entity\NomApiType 
     */
    public function getApitype()
    {
        return $this->apitype;
    }

    /**
     * Set driver
     *
     * @param \FrontendBundle\Entity\NomDriver $driver
     * @return DatApi
     */
    public function setDriver(\FrontendBundle\Entity\NomDriver $driver)
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * Get driver
     *
     * @return \FrontendBundle\Entity\NomDriver 
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Set db_user
     *
     * @param string $dbUser
     * @return DatApi
     */
    public function setDbUser($dbUser)
    {
        $this->db_user = $dbUser;

        return $this;
    }

    /**
     * Get db_user
     *
     * @return string 
     */
    public function getDbUser()
    {
        return $this->db_user;
    }

    /**
     * Get db_user
     *
     * @return string
     */
    public function getDb_user()
    {
        return $this->db_user;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }


}
