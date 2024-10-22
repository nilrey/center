<?php

namespace App\NCUO\FoivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoivFdo
 *
 * @ORM\Table(name="eif_data2.dict_roiv_fdo", indexes={@ORM\Index(name="fki_fd_fk", columns={"federal_district"}), @ORM\Index(name="IDX_F1BAEFFBD8F9EC2E", columns={"foiv_fk"}), @ORM\Index(name="IDX_F1BAEFFBECEAD01", columns={"supervisor_fk"})})
 * @ORM\Entity
 */
class RoivFdo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="eif_data2.seq_fdo", allocationSize=1, initialValue=1)
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
     * @ORM\Column(name="short_name", type="string", length=255, nullable=true)
     */
    private $shortName;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="website", type="string", length=255, nullable=true)
     */
    private $website;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="comments", type="text", nullable=true)
     */
    private $comments;

    /**
     * @var NCUO\FoivBundle\Entity\Roiv
     *
     * @ORM\ManyToOne(targetEntity="NCUO\FoivBundle\Entity\Roiv")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fk_roiv", referencedColumnName="id")
     * })
     */
    private $roivFk;

    /**
     * @var NCUO\FoivBundle\Entity\RoivFdoPersons
     *
     * @ORM\ManyToOne(targetEntity="NCUO\FoivBundle\Entity\RoivFdoPersons")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="supervisor_fk", referencedColumnName="id")
     * })
     */
    private $supervisorFk;

    /**
     * @var NCUO\FoivBundle\Entity\FederalDistricts
     *
     * @ORM\ManyToOne(targetEntity="NCUO\FoivBundle\Entity\FederalDistricts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="federal_district", referencedColumnName="id")
     * })
     */
    private $federalDistrict;

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
     * @return RoivFdo
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
     * Set shortName
     *
     * @param string $shortName
     * @return RoivFdo
     */
    public function setShortName($shortName)
    {
        $this->shortName = $shortName;

        return $this;
    }

    /**
     * Get shortName
     *
     * @return string 
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return RoivFdo
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string 
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return RoivFdo
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set website
     *
     * @param string $website
     * @return RoivFdo
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string 
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return RoivFdo
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set comments
     *
     * @param string $comments
     * @return RoivFdo
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get comments
     *
     * @return string 
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set roivFk
     *
     * @param \NCUO\FoivBundle\Entity\Roiv $roivFk
     * @return RoivFdo
     */
    public function setRoivFk(\NCUO\FoivBundle\Entity\Roiv $roivFk = null)
    {
        $this->roivFk = $roivFk;

        return $this;
    }

    /**
     * Get roivFk
     *
     * @return \NCUO\FoivBundle\Entity\Roiv 
     */
    public function getRoivFk()
    {
        return $this->roivFk;
    }

    /**
     * Set supervisorFk
     *
     * @param \NCUO\FoivBundle\Entity\RoivFdoPersons $supervisorFk
     * @return RoivFdo
     */
    public function setSupervisorFk(\NCUO\FoivBundle\Entity\RoivFdoPersons $supervisorFk = null)
    {
        $this->supervisorFk = $supervisorFk;

        return $this;
    }

    /**
     * Get supervisorFk
     *
     * @return \NCUO\FoivBundle\Entity\RoivFdoPersons 
     */
    public function getSupervisorFk()
    {
        return $this->supervisorFk;
    }

    /**
     * Set federalDistrict
     *
     * @param \NCUO\FoivBundle\Entity\FederalDistricts $federalDistrict
     * @return RoivFdo
     */
    public function setFederalDistrict(\NCUO\FoivBundle\Entity\FederalDistricts $federalDistrict = null)
    {
        $this->federalDistrict = $federalDistrict;

        return $this;
    }

    /**
     * Get federalDistrict
     *
     * @return \NCUO\FoivBundle\Entity\FederalDistricts 
     */
    public function getFederalDistrict()
    {
        return $this->federalDistrict;
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
