<?php

namespace App\NCUO\FoivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\NCUO\FoivBundle\Entity\FoivPerson;

/**
 * FoivPersons
 *
 * @ORM\Table(name="eif_data2.dict_foiv_persons", indexes={@ORM\Index(name="IDX_595C913D4D9192F8", columns={"supervisor"}), @ORM\Index(name="IDX_595C913DFDE52107", columns={"fk_foiv"})})
 * @ORM\Entity(repositoryClass="FoivPersonsRepository")
 */
class FoivPersons
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="eif_data2.dict_foiv_persons", allocationSize=1, initialValue=1)
     */
    private $id;

	 ////////////////////////////////////////////////////
	 // Для совместивости старые поля временно оставлены.
	 // Позже данные старые поля будут удалены из PHP-кода 
	 // и из таблицы dict_foiv_persons
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
     * @var App\NCUO\FoivBundle\Entity\FoivPersons
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\FoivPersons")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="supervisor", referencedColumnName="id")
     * })
     */
    private $supervisor;

    /**
     * @var App\NCUO\FoivBundle\Entity\Foiv
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\Foiv", inversedBy="foiv_persons")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fk_foiv", referencedColumnName="id")
     * })
     */
    private $fkFoiv;

    /**
     * @var App\NCUO\FoivBundle\Entity\File
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\File",cascade={"persist"})
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
     * @var boolean
     *
     * @ORM\Column(name="showed", type="boolean", nullable=false)
     */  
	protected $showed;
	
	/**
     * @var integer
     *
     * @ORM\Column(name="weight", type="integer", nullable=false)
     */  
	protected $weight;
    
    
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fio
     *
     * @param string $fio
     * @return FoivPersons
     */
    public function setFio($fio)
    {
		//$this->person->setFIO($fio);     
		//временно сохраняем старым способом
        $this->fio = $fio;  // Убрал person Деревянский И.В 08.10.2019

        return $this;
    }

    /**
     * Get fio
     *
     * @return string 
     */
    public function getFio()
    {
        return $this->fio; // Убрал person Деревянский И.В 08.10.2019
		//if($this->person != null)
		//{
			//return $this->person->getFIO();
		//}
		
		//return null;
    }

    
    /**
     * Set surname
     *
     * @param string $Surname
     * @return FoivPerson
     */
    public function setSurname($Surname)
    {
        
        // Несуществующее поле Surname Деревянский И.В 08.10.2019
        
		//if($this->person != null)
		//{
			//return $this->person->setSurname($Surname);
			//для совместимости сохраняем старым способом
			//$this->fio = $this->person->getFIO();
		//}
		       
        //return $this;
    
    }
    
    
	 /**
     * Get surname
     *
     * @return string 
     */
    public function getSurname()
    {
        
        // Несуществующее поле Surname Деревянский И.В 08.10.2019
        
        //if($this->person != null)
		//{
			//return $this->person->getSurname();
		//}
		
		//return null;
    }
        
    
	
	 /**
     * Get surname
     *
     * @return string 
     */
    public function getName()
    {
        
        // Несуществующее поле Surname Деревянский И.В 08.10.2019
        
        //if($this->person != null)
		//{
			//return $this->person->getName();
		//}
		
		//return null;
    }
        
    /**
     * Set surname
     *
     * @param string $Surname
     * @return FoivPerson
     */
    public function setName($Name)
    {
        
        // Несуществующее поле Surname Деревянский И.В 08.10.2019
        
        
		//if($this->person != null)
		//{
			//return $this->person->setName($Name);
			//для совместимости сохраняем старым способом
			//$this->fio = $this->person->getFIO();
		//}
		       

        //return $this;
    }
	
	 /**
     * Get patronymic
     *
     * @return string 
     */
    public function getPatronymic()
    {
        // Несуществующее поле patronymic Деревянский И.В 08.10.2019
        
		//if($this->person != null)
		//{
			//return $this->person->getPatronymic();
		//}
		
        //return null;
    }
    
     /**
     * Set patronymic 
     *
     * @param string $Patronymic
     * @return FoivPerson
     */
    public function setPatronymic($Patronymic)
    {
        
        // Несуществующее поле patronymic Деревянский И.В 08.10.2019
        
        
		//if($this->person != null)
		//{
			//$this->person->setPatronymic($Patronymic);
			//для совместимости сохраняем старым способом
			//$this->fio = $this->person->getFIO();
		//}
        		
        //return $this;
    }
	
    /**
     * Set position
     *
     * @param string $position
     * @return FoivPersons
     */
    public function setPosition($position)
    {
        
        // Убрал person Деревянский И.В 08.10.2019
        
		//$this->person->setPosition($position);
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
		if($this->position != null)
		{
			return $this->position;
		}
		
		return null;
        //return $this->position;
    }

    /**
     * Set photo
     *
     * @param string $photo
     * @return FoivPersons
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
     * @return FoivPersons
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
        if($this->photoUrl != null)
		{
			return $this->photoUrl;
		}
		
		return null;      
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return FoivPersons
     */
    public function setPhone($phone)
    {
        
        // Убрал person Деревянский И.В 08.10.2019
        
		//$this->person->setPhone($phone);
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
        //return $this->phone;
		if($this->phone != null)
		{
			return $this->phone;
		}
		
		return null;
    }

    /**
     * Set websiteUrl
     *
     * @param string $websiteUrl
     * @return FoivPersons
     */
    public function setWebsiteUrl($websiteUrl)
    {
        
        // Убрал person Деревянский И.В 08.10.2019
        
		 //$this->person->setWebsiteUrl($websiteUrl);
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
        //return $this->websiteUrl;
		if($this->websiteUrl != null)
		{
			return $this->websiteUrl;
		}
		
		return null;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return FoivPersons
     */
    public function setAddress($address)
    {
        // Убрал person Деревянский И.В 08.10.2019
        
		 //$this->person->setAddress($address);
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
		if($this->address != null)
		{
			return $this->address;
		}
		
		return null;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return FoivPersons
     */
    public function setEmail($email)
    {
        
        // Убрал person Деревянский И.В 08.10.2019
        
		//$this->person->setEmail($email);
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
        //return $this->email;
		if($this->email != null)
		{
			return $this->email;
		}
		
		return null;
    }

    /**
     * Set supervisor
     *
     * @param \NCUO\FoivBundle\Entity\FoivPersons $supervisor
     * @return FoivPersons
     */
    public function setSupervisor(App\NCUO\FoivBundle\Entity\FoivPersons $supervisor = null)
    {
        $this->supervisor = $supervisor;

        return $this;
    }

    /**
     * Get supervisor
     *
     * @return \NCUO\FoivBundle\Entity\FoivPersons 
     */
    public function getSupervisor()
    {
        return $this->supervisor;
    }

    /**
     * Set fkFoiv
     *
     * @param \NCUO\FoivBundle\Entity\Foiv $fkFoiv
     * @return FoivPersons
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
     * @return \NCUO\FoivBundle\Entity\File 
     */
    public function getPhotoId()
    {
        
        // Убрал person Деревянский И.В 08.10.2019
        
		//в будущем вместо поля PhotoId д.б. обращение к сущности File через FoivPerson
		//return $this->person->getPhotoFile()->getId();
        return $this->photo_id;
    }
    
    public function getPhotoFilename($upload_dir) {
        if (file_exists($upload_dir . "/persons/" . $this->getId() . ".png"))
            return $this->getId() . ".png";
        else
            return "no-user.jpg";
    }
    
    public function __toString()
    {
    	return $this->getFio() . "";
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
    
	/**
     * Получение значение флага отображения
	 * на главной странице
     *
     * @return boolean
     */
    public function getShowed()
	{
		return $this->showed;
	}
	
	/**
     * Установка значения флага отображения
	 * на главной странице
	 *
     * @param $IsShowed
     * @return FoivPersons
     */
	public function setShowed($IsShowed)
	{
		 $this->showed = $IsShowed;
		 return $this;
	}
	
	/**
    * Получение значение веса
    *
    * @return integer
    */
	public function getWeight()
	{
		return $this->weight;
	}
	
	/**
     * Установка нового значения веса
	 *
     * @param $Weight
     * @return FoivPersons
     */
	public function setWeight($Weight)
	{
			if(is_int((int)$Weight) )
			{
				if($Weight >= 0 && $Weight <= 99)
				{
					return $this->weight = (int)$Weight;
				}
				else
				{
					throw new \Exception("Вес д.б. в диапазоне от 0 до 99");
				}
			}
			else
			{
				throw new \Exception("Вес д.б. целочисленным значением");
			}
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
		 //позже надо добавить сохранение фото
		 
         return $this;
    }
}
