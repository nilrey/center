<?php

namespace App\NCUO\FoivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\NCUO\FoivBundle\Entity\FoivPerson;

/**
 * SitcenterPerson
 *
 * @ORM\Table(name="eif_data2.dict_sitcenter_person")
 * @ORM\Entity(repositoryClass="NCUO\FoivBundle\Entity\SitcenterPersonRepository")
 */
class SitcenterPerson
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="eif_data2.seq_sitcenter_person", allocationSize=1, initialValue=1)
     */
    private $id;

	 ////////////////////////////////////////////////////
	 // Для совместивости старые поля временно оставлены.
	 // Позже данные старые поля будут удалены из PHP-кода 
	 // и из таблицы dict_sitcenter_person
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
     * @ORM\Column(name="photo_url", type="text", nullable=true)
     */
    private $photoUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var integer
     *
     * @ORM\Column(name="sitcenter_id", type="integer", nullable=true)
     */
    private $sitcenterId;
	
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
     * @return SitcenterPerson
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
     * @return SitcenterPerson
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
        //return $this->position;
		return $this->person->getPosition();
    }

    /**
     * Set photoUrl
     *
     * @param string $photoUrl
     * @return SitcenterPerson
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
		//В будущем все фото будут исзлекаться из таблицы dict_files
		//Пока берем старым способом
        return $this->photoUrl;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return SitcenterPerson
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
     * Set email
     *
     * @param string $email
     * @return SitcenterPerson
     */
    public function setEmail($email)
    {
		//временно сохраняем старым способом
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
     * Set sitcenterId
     *
     * @param integer $sitcenterId
     * @return SitcenterPerson
     */
    public function setSitcenterId($sitcenterId)
    {
        $this->sitcenterId = $sitcenterId;

        return $this;
    }

    /**
     * Get sitcenterId
     *
     * @return integer 
     */
    public function getSitcenterId()
    {
        return $this->sitcenterId;
    }
	
	 /**
     * Get person
     *
     * @return App\NCUO\FoivBundle\Entity\FoivPerson
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
		 
         return $this;
    }
}
