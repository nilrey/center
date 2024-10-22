<?php

namespace App\NCUO\FoivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\NCUO\FoivBundle\Entity\FoivPerson;

/**
 * FoivDptPersons
 *
 * @ORM\Table(name="eif_data2.dict_foiv_dpt_persons", indexes={@ORM\Index(name="IDX_1744F54DFDE52107", columns={"fk_foiv"})})
 * @ORM\Entity(repositoryClass="NCUO\FoivBundle\Entity\FoivDptPersonsRepository")
 */
class FoivDptPersons
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="eif_data2.seq_foiv_dpt_persons", allocationSize=1, initialValue=1)
     */
    private $id;
	
	 ////////////////////////////////////////////////////
	 // Для совместивости старые поля временно оставлены.
	 // Позже данные старые поля будут удалены из PHP-кода 
	 // и из таблицы dict_foiv_dpt_persons
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
     * @var App\NCUO\FoivBundle\Entity\FoivPersons
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\File")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="photo_id", referencedColumnName="id")
     * })
     */
    private $photo_id;
	
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
     * @return FoivDptPersons
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
		return $this->person->getFIO();
		
        //return $this->fio;
		
    }

    /**
     * Set position
     *
     * @param string $position
     * @return FoivDptPersons
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
     * @return FoivDptPersons
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
     * @return FoivDptPersons
     */
    public function setPhotoUrl($photoUrl)
    {
		//временно сохраняем старым способом
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
     * @return FoivDptPersons
     */
    public function setPhone($phone)
    {
		$this->person->setPhone($phone);
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
     * @return FoivDptPersons
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
     * @return FoivDptPersons
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
		$this->person->getAddress();
        //return $this->address;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return FoivDptPersons
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
     * Set supervisor
     *
     * @param integer $supervisor
     * @return FoivDptPersons
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
     * @param App\NCUO\FoivBundle\Entity\Foiv $fkFoiv
     * @return FoivDptPersons
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
     * Set photo_id
     *
     * @param integer $photo_id
     * @return FoivDptPersons
     */
    public function setPhotoId( $photo_id )
    {
        $this->photo_id = $photo_id;

        return $this;
    }

    /**
     * Get photo_id
     *
     * @return \NCUO\FoivBundle\Entity\FoivPersons 
     */
    public function getPhotoId()
    {
		//в будущем вместо поля PhotoId д.б. обращение к сущности File через FoivPerson
		//return $this->person->getPhotoFile()->getId();
        return $this->photo_id;
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
     * @param App\NCUO\FoivBundle\Entity\FoivPerson $Person
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
		 //позже надо добавить сохранение фото
		 
         return $this;
    }
    
}
