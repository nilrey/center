<?php

namespace App\NCUO\FoivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\NCUO\FoivBundle\Entity\FoivPerson;

/**
 * FoivFdoPersons
 *
 * @ORM\Table(name="eif_data2.dict_foiv_fdo_persons", indexes={@ORM\Index(name="IDX_4FF8B1B2FDE52107", columns={"fk_foiv"})})
 * @ORM\Entity(repositoryClass="NCUO\FoivBundle\Entity\FoivFdoPersonsRepository")
 * 
 */
class FoivRdoPersons
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="eif_data2.seq_foiv_rdo_persons", allocationSize=1, initialValue=1)
     */
    private $id;

	 ////////////////////////////////////////////////////
	 // ??? ????????????? ?????? ???? ???????? ?????????.
	 // ????? ?????? ?????? ???? ????? ??????? ?? PHP-???? 
	 // ? ?? ??????? dict_foiv_fdo_persons
	 ////////////////////////////////////////////////////
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
     * @ORM\Column(name="photo_url", type="text", nullable=true)
     */
    private $photoUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="text", nullable=true)
     */
    private $phone;

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
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var integer
     *
     * @ORM\Column(name="supervisor", type="integer", nullable=true)
     */
    private $supervisor;

    /**
     * @var App\NCUO\FoivBundle\Entity\Foiv
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\Foiv")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fk_foiv", referencedColumnName="id")
     * })
     */
    private $fkFoiv;


	/**
     * @var App\NCUO\FoivBundle\Entity\FoivPerson
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\FoivPerson")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     * })
     */
     private $person;

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
     * @return FoivFdoPersons
     */
    public function setFio($fio)
    {
		$this->person->setFIO($fio);
		//???????? ????????? ?????? ????????
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
		return $this->person->getFIO();
		
        //return $this->fio;
    }

    /**
     * Set position
     *
     * @param string $position
     * @return FoivFdoPersons
     */
    public function setPosition($position)
    {
		
		$this->person->setPosition($position);
		
		//???????? ????????? ?????? ????????
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
        //return $this->position;
		return $this->person->getPosition();
    }

    /**
     * Set photo
     *
     * @param string $photo
     * @return FoivFdoPersons
     */
    public function setPhoto($photo)
    {
		//???????? ????????? ?????? ????????
        $this->photo = $photo;

		//? ??????? ??? ???? ????? ????????? ? ??????? dict_files
        return $this;
    }

    /**
     * Get photo
     *
     * @return string 
     */
    public function getPhoto()
    {
		//? ??????? ??? ???? ????? ??????????? ?? ??????? dict_files
		//???? ????? ?????? ????????
        return $this->photo;
		
    }

    /**
     * Set photoUrl
     *
     * @param string $photoUrl
     * @return FoivFdoPersons
     */
    public function setPhotoUrl($photoUrl)
    {
		//? ??????? ??? ???? ????? ????????? ? ??????? dict_files
		//? ???? ???????? ????????? ?????? ????????
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
		//? ??????? ??? ???? ????? ??????????? ?? ??????? dict_files
		//???? ????? ?????? ????????
        return $this->photoUrl;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return FoivFdoPersons
     */
    public function setPhone($phone)
    {
		//???????? ????????? ?????? ????????
        $this->phone = $phone;
		$this->person->setPhone();

        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
		return $this->person->getPhone();
        //return $this->phone;
    }

    /**
     * Set websiteUrl
     *
     * @param string $websiteUrl
     * @return FoivFdoPersons
     */
    public function setWebsiteUrl($websiteUrl)
    {
		//???????? ????????? ?????? ????????
        $this->websiteUrl = $websiteUrl;
		$this->person->setWebsiteUrl($websiteUrl);

        return $this;
    }

    /**
     * Get websiteUrl
     *
     * @return string 
     */
    public function getWebsiteUrl()
    {
       // return $this->websiteUrl;
	   return $this->person->getWebsiteUrl();
    }

    /**
     * Set address
     *
     * @param string $address
     * @return FoivFdoPersons
     */
    public function setAddress($address)
    {
		//???????? ????????? ?????? ????????
        $this->address = $address;
		$this->person->setAddress($address);
		
        return $this;
    }

    /**
     * Get address
     *
     * @return string 
     */
    public function getAddress()
    {
        //return $this->address;
		return $this->person->getAddress();
    }

    /**
     * Set email
     *
     * @param string $email
     * @return FoivFdoPersons
     */
    public function setEmail($email)
    {
		//???????? ????????? ?????? ????????
        $this->email = $email;
		$this->person->setEmail($email);
		
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        //return $this->email;
		return $this->person->getEmail();
    }

    /**
     * Set supervisor
     *
     * @param integer $supervisor
     * @return FoivFdoPersons
     */
    public function setSupervisor($supervisor)
    {
        $this->supervisor = $supervisor;

        return $this;
    }

    /**
     * Get supervisor
     *
     * @return integer 
     */
    public function getSupervisor()
    {
        return $this->supervisor;
    }

    /**
     * Set fkFoiv
     *
     * @param \NCUO\FoivBundle\Entity\Foiv $fkFoiv
     * @return FoivFdoPersons
     */
    public function setFkFoiv(App\NCUO\FoivBundle\Entity\Foiv $fkFoiv = null)
    {
        $this->fkFoiv = $fkFoiv;

        return $this;
    }

    /**
     * Get fkFoiv
     *
     * @return \NCUO\FoivBundle\Entity\Foiv 
     */
    public function getFkFoiv()
    {
        return $this->fkFoiv;
    }
	
	 /**
     * Get person
     *
     * @return \NCUO\FoivBundle\Entity\FoivPerson
     */
    public function getPerson()
    {
		if($this->person == null)
		{
			return null;
		}
		
        return $this->person;
    }
    
	public function getPersonId()
	{
		if($this->getPerson() == null)
		{
			return null;
		}
		
		return $this->getPerson()->getId();
	}
	
     /**
     * Set FoivPerson
     *
     * @param \NCUO\FoivBundle\Entity\FoivPerson $Person
     * @return FoivContacts
     */
    public function setPerson(App\NCUO\FoivBundle\Entity\FoivPerson $Person)
    {
         $this->person = $Person;
		 
		 $this->fio 		 = $this->person->getFIO();
		 $this->position 	 = $this->person->getPosition();
		 $this->phone 		 = $this->person->getPhone();
		 $this->email 		 = $this->person->getEmail();
		 $this->address 	 = $this->person->getAddress();
		 $this->websiteUrl   = $this->person->getWebsiteUrl();
         return $this;
    }
}
