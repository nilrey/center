<?php

namespace App\NCUO\FoivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoivPersons
 *
 * @ORM\Table(name="eif_data2.dict_roiv_persons", uniqueConstraints={@ORM\UniqueConstraint(name="dict_roiv_persons_id_key", columns={"id"})}, indexes={@ORM\Index(name="IDX_564247BECDDEC431", columns={"id_roiv_department"}), @ORM\Index(name="IDX_564247BE258023F3", columns={"id_roiv"})})
 * @ORM\Entity
 */
class RoivPersons
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="eif_data2.dict_roiv_persons_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="fio", type="string", length=255, nullable=true)
     */
    private $fio;

    /**
     * @var string
     *
     * @ORM\Column(name="position", type="string", length=255, nullable=true)
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(name="photo", type="blob", nullable=true)
     */
    private $photo;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="text", nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="text", nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="website_url", type="string", length=255, nullable=true)
     */
    private $websiteUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="photo_url", type="text", nullable=true)
     */
    private $photoUrl;

    /**
     * @var NCUO\FoivBundle\Entity\RoivDepartments
     *
     * @ORM\ManyToOne(targetEntity="NCUO\FoivBundle\Entity\RoivDepartments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_roiv_department", referencedColumnName="id")
     * })
     */
    private $idRoivDepartment;

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
     * Set fio
     *
     * @param string $fio
     * @return RoivPersons
     */
    public function setFio($fio)
    {
        $this->fio = $fio;

        return $this;
    }

    /**
     * Get fio
     *
     * @return string 
     */
    public function getFio()
    {
        return $this->fio;
    }

    /**
     * Set position
     *
     * @param string $position
     * @return RoivPersons
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return string 
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set photo
     *
     * @param string $photo
     * @return RoivPersons
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * Get photo
     *
     * @return string 
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return RoivPersons
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
     * Set email
     *
     * @param string $email
     * @return RoivPersons
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
     * Set websiteUrl
     *
     * @param string $websiteUrl
     * @return RoivPersons
     */
    public function setWebsiteUrl($websiteUrl)
    {
        $this->websiteUrl = $websiteUrl;

        return $this;
    }

    /**
     * Get websiteUrl
     *
     * @return string 
     */
    public function getWebsiteUrl()
    {
        return $this->websiteUrl;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return RoivPersons
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
     * Set photoUrl
     *
     * @param string $photoUrl
     * @return RoivPersons
     */
    public function setPhotoUrl($photoUrl)
    {
        $this->photoUrl = $photoUrl;

        return $this;
    }

    /**
     * Get photoUrl
     *
     * @return string 
     */
    public function getPhotoUrl()
    {
        return $this->photoUrl;
    }

    /**
     * Set idRoivDepartment
     *
     * @param \NCUO\FoivBundle\Entity\RoivDepartments $idRoivDepartment
     * @return RoivPersons
     */
    public function setIdRoivDepartment(\NCUO\FoivBundle\Entity\RoivDepartments $idRoivDepartment = null)
    {
        $this->idRoivDepartment = $idRoivDepartment;

        return $this;
    }

    /**
     * Get idRoivDepartment
     *
     * @return \NCUO\FoivBundle\Entity\RoivDepartments 
     */
    public function getIdRoivDepartment()
    {
        return $this->idRoivDepartment;
    }

    /**
     * Set idRoiv
     *
     * @param \NCUO\FoivBundle\Entity\Roiv $idRoiv
     * @return RoivPersons
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
