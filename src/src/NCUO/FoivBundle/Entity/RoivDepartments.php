<?php

namespace App\NCUO\FoivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoivDepartments
 *
 * @ORM\Table(name="eif_data2.dict_roiv_departments", indexes={@ORM\Index(name="IDX_504685501E90D3F0", columns={"director"}), @ORM\Index(name="IDX_504685507FE4B2B", columns={"id_type"}), @ORM\Index(name="IDX_50468550258023F3", columns={"id_roiv"})})
 * @ORM\Entity
 */
class RoivDepartments
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="eif_data2.dict_roiv_departments_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var NCUO\FoivBundle\Entity\RoivPersons
     *
     * @ORM\ManyToOne(targetEntity="NCUO\FoivBundle\Entity\RoivPersons")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="director", referencedColumnName="id")
     * })
     */
    private $director;

    /**
     * @var NCUO\FoivBundle\Entity\DepartmentTypes
     *
     * @ORM\ManyToOne(targetEntity="NCUO\FoivBundle\Entity\DepartmentTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_type", referencedColumnName="id")
     * })
     */
    private $idType;

    /**
     * @var NCUO\FoivBundle\Entity\Roiv
     *
     * @ORM\ManyToOne(targetEntity="NCUO\FoivBundle\Entity\Roiv")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_roiv", referencedColumnName="id")
     * })
     */
    private $idRoiv;



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
     * @return RoivDepartments
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
     * Set director
     *
     * @param \NCUO\FoivBundle\Entity\RoivPersons $director
     * @return RoivDepartments
     */
    public function setDirector(\NCUO\FoivBundle\Entity\RoivPersons $director = null)
    {
        $this->director = $director;

        return $this;
    }

    /**
     * Get director
     *
     * @return \NCUO\FoivBundle\Entity\RoivPersons 
     */
    public function getDirector()
    {
        return $this->director;
    }

    /**
     * Set idType
     *
     * @param \NCUO\FoivBundle\Entity\DepartmentTypes $idType
     * @return RoivDepartments
     */
    public function setIdType(\NCUO\FoivBundle\Entity\DepartmentTypes $idType = null)
    {
        $this->idType = $idType;

        return $this;
    }

    /**
     * Get idType
     *
     * @return \NCUO\FoivBundle\Entity\DepartmentTypes 
     */
    public function getIdType()
    {
        return $this->idType;
    }

    /**
     * Set idRoiv
     *
     * @param \NCUO\FoivBundle\Entity\Roiv $idRoiv
     * @return RoivDepartments
     */
    public function setIdRoiv(\NCUO\FoivBundle\Entity\Roiv $idRoiv = null)
    {
        $this->idRoiv = $idRoiv;

        return $this;
    }

    /**
     * Get idRoiv
     *
     * @return \NCUO\FoivBundle\Entity\Roiv 
     */
    public function getIdRoiv()
    {
        return $this->idRoiv;
    }
}
