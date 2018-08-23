<?php

namespace FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use FrontendBundle\Entity\Nomenclator\Nomenclator;

/**
 * NomDriver
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="FrontendBundle\Entity\NomDriverRepository")
 */
class NomDriver implements Nomenclator
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
     * @ORM\OneToMany(targetEntity="DatApi", mappedBy="driver")
     */
    protected $apis;

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
     * @return NomDriver
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
     * @return NomDriver
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
     * Constructor
     */
    public function __construct()
    {
        $this->apis = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add apis
     *
     * @param \FrontendBundle\Entity\DatApi $apis
     * @return NomDriver
     */
    public function addApi(\FrontendBundle\Entity\DatApi $apis)
    {
        $this->apis[] = $apis;

        return $this;
    }

    /**
     * Remove apis
     *
     * @param \FrontendBundle\Entity\DatApi $apis
     */
    public function removeApi(\FrontendBundle\Entity\DatApi $apis)
    {
        $this->apis->removeElement($apis);
    }

    /**
     * Get apis
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getApis()
    {
        return $this->apis;
    }
}
