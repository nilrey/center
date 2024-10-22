<?php

namespace App\NCUO\FoivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FoivDepartments
 *
 * @ORM\Table(name="eif_data2.dict_foiv_departments", indexes={@ORM\Index(name="IDX_6250F74919E9AC5F", columns={"supervisor_id"}), @ORM\Index(name="IDX_6250F749C54C8C93", columns={"type_id"}), @ORM\Index(name="IDX_6250F749CFDEED70", columns={"foiv_id"})})
 * @ORM\Entity
 */
class FoivDepartments
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="eif_data2.seq_foiv_departments", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="functions", type="text", nullable=true)
     */
    private $functions;

    /**
     * @var App\NCUO\FoivBundle\Entity\FoivDptPersons
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\FoivDptPersons")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="supervisor_id", referencedColumnName="id")
     * })
     */
    private $supervisor;

    /**
     * @var App\NCUO\FoivBundle\Entity\DepartmentTypes
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\DepartmentTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     * })
     */
    private $type;

    /**
     * @var App\NCUO\FoivBundle\Entity\Foiv
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\Foiv")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="foiv_id", referencedColumnName="id")
     * })
     */
    private $foiv;

    /**
     * @var DateTime 
     *
     * @ORM\Column(name="created", type="string", nullable=false)
     */       
    
    protected $create_date;
    /**
     * @var DateTime 
     *
     * @ORM\Column(name="modified", type="string", nullable=false)
     */       
    protected $modified_date;
    


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
     * @return FoivDepartments
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
     * Set functions
     *
     * @param string $functions
     * @return FoivDepartments
     */
    public function setFunctions($functions)
    {
        $this->functions = $functions;

        return $this;
    }

    /**
     * Get functions
     *
     * @return string 
     */
    public function getFunctions()
    {
        return $this->functions;
    }

    /**
     * Set supervisor
     *
     * @param App\NCUO\FoivBundle\Entity\FoivDptPersons $supervisor
     * @return FoivDepartments
     */
    public function setSupervisor(App\NCUO\FoivBundle\Entity\FoivDptPersons $supervisor = null)
    {
        $this->supervisor = $supervisor;

        return $this;
    }

    /**
     * Get supervisor
     *
     * @return App\NCUO\FoivBundle\Entity\FoivDptPersons 
     */
    public function getSupervisor()
    {
        return $this->supervisor;
    }

    /**
     * Set type
     *
     * @param App\NCUO\FoivBundle\Entity\DepartmentTypes $type
     * @return FoivDepartments
     */
    public function setType(App\NCUO\FoivBundle\Entity\DepartmentTypes $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return App\NCUO\FoivBundle\Entity\DepartmentTypes 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set foiv
     *
     * @param App\NCUO\FoivBundle\Entity\Foiv $foiv
     * @return FoivDepartments
     */
    public function setFoiv(App\NCUO\FoivBundle\Entity\Foiv $foiv = null)
    {
        $this->foiv = $foiv;

        return $this;
    }

    /**
     * Get foiv
     *
     * @return App\NCUO\FoivBundle\Entity\Foiv 
     */
    public function getFoiv()
    {
        return $this->foiv;
    }
    
    /**
     * Get create date
     *
     * @return String 
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }
    
    /**
     * Get modified date
     *
     * @return String 
     */
    public function getModifiedDate()
    {
        return $this->modified_date;
    }
    
}
