<?php

namespace FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FrontendBundle\Entity\DatProject;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DatUrl
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="FrontendBundle\Entity\DatUrlRepository")
 */
class DatUrl
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
     * @ORM\Column(name="dir", type="string", nullable=false)
     */
    private $dir;

    /**
     * @ORM\ManyToOne(targetEntity="DatProject", inversedBy="urls", cascade={"remove"})
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", nullable=false)
     */
    protected $project;

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
     * Set dir
     *
     * @param string $dir
     * @return DatUrl
     */
    public function setDir($dir)
    {
        $this->dir = $dir;

        return $this;
    }

    /**
     * Get dir
     *
     * @return string 
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * Set project
     *
     * @param DatProject $project
     * @return DatUrl
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
