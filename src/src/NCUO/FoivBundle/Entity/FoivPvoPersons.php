<?php

namespace App\NCUO\FoivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FoivPvoPersons
 *
 * @ORM\Table(name="eif_data2.dict_foiv_pvo_persons", indexes={@ORM\Index(name="IDX_2AE0EEEF6EE9767", columns={"fk_foiv_pvo"})})
 * @ORM\Entity(repositoryClass="NCUO\FoivBundle\Entity\FoivPvoPersonsRepository")
 */
class FoivPvoPersons
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="eif_data2.seq_foiv_pvo_persons", allocationSize=1, initialValue=1)
     */
    private $id;

	////////////////////////////////////////////////////
	 // Для совместивости старые поля временно оставлены.
	 // Позже данные старые поля будут удалены из PHP-кода 
	 // и из таблицы dict_foiv_pvo_persons
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
     * @ORM\Column(name="biography", type="text", nullable=true)
     */
    private $biography;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var App\NCUO\FoivBundle\Entity\FoivPvo
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\FoivPvo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fk_foiv_pvo", referencedColumnName="id")
     * })
     */
    private $fkFoivPvo;


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
     * @return FoivPvoPersons
     */
    public function setFio($fio)
    {
		$this->person->setFIO($fio);
		//временно сохраняем старым способом
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
        //return $this->fio;
		return $this->person->getFIO();
    }

    /**
     * Set position
     *
     * @param string $position
     * @return FoivPvoPersons
     */
    public function setPosition($position)
    {
		$this->person->setPosition($position);
		//временно сохраняем старым способом
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
		return $this->person->getPosition();
        //return $this->position;
    }

    /**
     * Set photo
     *
     * @param string $photo
     * @return FoivPvoPersons
     */
    public function setPhoto($photo)
    {
		//временно сохраняем старым способом
        $this->photo = $photo;
		//В будущем все фото будут храниться в таблице dict_files
		
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
     * Set photoUrl
     *
     * @param string $photoUrl
     * @return FoivPvoPersons
     */
    public function setPhotoUrl($photoUrl)
    {
        $this->photoUrl = $photoUrl;
		//В будущем все фото будут храниться в таблице dict_files
		
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
     * Set phone
     *
     * @param string $phone
     * @return FoivPvoPersons
     */
    public function setPhone($phone)
    {
		$this->person->setPhone();
		//временно сохраняем старым способом
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
		return $this->person->getPhone();
        //return $this->phone;
    }

    /**
     * Set websiteUrl
     *
     * @param string $websiteUrl
     * @return FoivPvoPersons
     */
    public function setWebsiteUrl($websiteUrl)
    {
		 $this->person->setWebsiteUrl($websiteUrl);
		//временно сохраняем старым способом
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
		return $this->person->getWebsiteUrl();
        //return $this->websiteUrl;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return FoivPvoPersons
     */
    public function setAddress($address)
    {
		 $this->person->setAddress($address);
		//временно сохраняем старым способом
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
		
        //return $this->address;
    }

    /**
     * Set biography
     *
     * @param string $biography
     * @return FoivPvoPersons
     */
    public function setBiography($biography)
    {
		 $this->person->setBiography($biography);
		//временно сохраняем старым способом
        $this->biography = $biography;

        return $this;
    }

    /**
     * Get biography
     *
     * @return string 
     */
    public function getBiography()
    {
		$this->person->getBiography();
        //return $this->biography;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return FoivPvoPersons
     */
    public function setEmail($email)
    {
		$this->person->setEmail($email);
		//временно сохраняем старым способом
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
		return $this->person->getEmail();
        //return $this->email;
    }

    /**
     * Set fkFoivPvo
     *
     * @param \NCUO\FoivBundle\Entity\FoivPvo $fkFoivPvo
     * @return FoivPvoPersons
     */
    public function setFkFoivPvo(App\NCUO\FoivBundle\Entity\FoivPvo $fkFoivPvo = null)
    {
        $this->fkFoivPvo = $fkFoivPvo;

        return $this;
    }

    /**
     * Get fkFoivPvo
     *
     * @return \NCUO\FoivBundle\Entity\FoivPvo 
     */
    public function getFkFoivPvo()
    {
        return $this->fkFoivPvo;
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
		 $this->biography 	 = $this->person->getBiography();
		 //позже надо добавить сохранение фото
		 
         return $this;
    }
    
    public function __toString()
    {
    	return $this->getFio();
    }
}
