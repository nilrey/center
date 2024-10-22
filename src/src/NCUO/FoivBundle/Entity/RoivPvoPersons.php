<?php

namespace App\NCUO\FoivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoivPvoPersons
 *
 * @ORM\Table(name="eif_data2.dict_roiv_pvo_persons", indexes={@ORM\Index(name="IDX_18F69CF69CEC07FD", columns={"id_roiv_pvo"})})
 * @ORM\Entity
 */
class RoivPvoPersons
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="eif_data2.dict_roiv_pvo_persons_id_seq", allocationSize=1, initialValue=1)
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
     * @var NCUO\FoivBundle\Entity\RoivPvo
     *
     * @ORM\ManyToOne(targetEntity="NCUO\FoivBundle\Entity\RoivPvo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_roiv_pvo", referencedColumnName="id")
     * })
     */
    private $idRoivPvo;



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
     * @return RoivPvoPersons
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
     * @return RoivPvoPersons
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
     * @return RoivPvoPersons
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
     * @return RoivPvoPersons
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
     * @return RoivPvoPersons
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
     * @return RoivPvoPersons
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
     * @return RoivPvoPersons
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
     * @return RoivPvoPersons
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
     * Set idRoivPvo
     *
     * @param \NCUO\FoivBundle\Entity\RoivPvo $idRoivPvo
     * @return RoivPvoPersons
     */
    public function setIdRoivPvo(\NCUO\FoivBundle\Entity\RoivPvo $idRoivPvo = null)
    {
        $this->idRoivPvo = $idRoivPvo;

        return $this;
    }

    /**
     * Get idRoivPvo
     *
     * @return \NCUO\FoivBundle\Entity\RoivPvo 
     */
    public function getIdRoivPvo()
    {
        return $this->idRoivPvo;
    }
}
