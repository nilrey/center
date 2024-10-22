<?php

namespace App\NCUO\FoivBundle\Entity;

use App\NCUO\FoivBundle\Entity\ORMObjectMetadata;
use Doctrine\ORM\Mapping as ORM;

/**
 * FoivPerson
 *
 * @ORM\Table(name="eif_data2.dict_person", indexes={@ORM\Index(name="dict_person_pkey", columns={"id"})})
 * @ORM\Entity
 */
class FoivPerson
{
    
    const SURNAME_COLUMN_NAME = "surname";
    const SURNAME_CONTROL_ID = "SURNAME";
    const SURNAME_CONTROL_CAPTION = "Фамилия";
    
    const NAME_COLUMN_NAME = "name";
    const NAME_CONTROL_ID = "NAME";
    const NAME_CONTROL_CAPTION = "Имя";
    
    const PATRONYMIC_COLUMN_NAME = "patronymic";
    const PATRONYMIC_CONTROL_ID = "PATRONYMIC";
    const PATRONYMIC_CONTROL_CAPTION = "Отчество";
    
    const ADDRESS_COLUMN_NAME = "address";
    const ADDRESS_CONTROL_ID = "ADDRESS";
    const ADDRESS_CONTROL_CAPTION = "Адрес";
   
    const PHOTO_FILE_COLUMN_NAME = "photo_id";
    const PHOTO_FILE_CONTROL_ID = "PHOTO_FILE";
    const PHOTO_FILE_CONTROL_CAPTION = "Фото";
    
    const PHONE_COLUMN_NAME = "phone";
    const PHONE_CONTROL_ID = "PHONE";
    const PHONE_CONTROL_CAPTION = "Телефон";
    
    const WEBSITE_URL_COLUMN_NAME = "website_url";
    const WEBSITE_URL_CONTROL_ID = "WEBSITE_URL";
    const WEBSITE_URL_CONTROL_CAPTION = "Веб-сайт";
    
    const EMAIL_COLUMN_NAME = "email";
    const EMAIL_CONTROL_ID = "EMAIL";
    const EMAIL_CONTROL_CAPTION = "E-mail";
    
    const POSITION_COLUMN_NAME = "position";
    const POSITION_CONTROL_ID = "POSITION";
    const POSITION_CONTROL_CAPTION = "Должность";
    
    const BIOGRAPHY_COLUMN_NAME = "biography";
    const BIOGRAPHY_CONTROL_ID = "BIOGRAPHY";
    const BIOGRAPHY_CONTROL_CAPTION = "E-mail";
	
	const SERVICE_COLUMN_NAME = "service";
    const SERVICE_CONTROL_ID = "SERVICE";
    const SERVICE_CONTROL_CAPTION = "Служба";
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="eif_data2.data_person", allocationSize=1, initialValue=1)
     */
    private $id;
    
     /**
     * @var string
     *
     * @ORM\Column(name="surname", type="string", length=255, nullable=false)
     */
    private $surname;
    
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;
    
    /**
     * @var string
     *
     * @ORM\Column(name="patronymic", type="string", length=128, nullable=false)
     */
    private $patronymic;
    
    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=1024, nullable=true)
     */
    private $address;
    
    /**
     * @var string
     *
     * @ORM\Column(name="photo_url", type="string", length=1024, nullable=true)
     */
    //private $photo_url;
    
   /**
     * @var integer
     *
     * @ORM\Column(name="photo_id", type="integer", nullable=true)
     */
    //private $photo_id;
    
     /**
     * @var App\NCUO\FoivBundle\Entity\File
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\File")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="photo_id", referencedColumnName="id")
     * })
     */
    private $photo_file;
    
     /**
     * @var string
     *
     * @ORM\Column(name="photo", type="blob", nullable=true)
     */
    //private $photo;
    
     /**
     * @var string
     *
     * @ORM\Column(name="phone", type="text", nullable=true)
     */
    private $phone;
    
     /**
     * @var string
     *
     * @ORM\Column(name="website_url", type="string", length=1024, nullable=true)
     */
    private $website_url;
    
     /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;
     
     /**
     * @var string
     *
     * @ORM\Column(name="position", type="string", length=255, nullable=true)
     */
    private $position;
    
      /**
     * @var string
     *
     * @ORM\Column(name="biography", type="text", nullable=true)
     */
    private $biography;
	
	/**
     * @var string
     *
     * @ORM\Column(name="service", type="string", length=256, nullable=true)
     */
	private $service;
    
   

	 
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

    public function getId()
    {
        return $this->id;
    }

	
	
     /**
     * Get surname
     *
     * @return string 
     */
    public function getSurname()
    {
        return $this->surname;
    }
        
    /**
     * Set surname
     *
     * @param string $Surname
     * @return FoivPerson
     */
    public function setSurname($Surname)
    {
        $this->surname = $Surname;

        return $this;
    }
	
	
    
    public static function getSurnameControlID()
    {
        return self::SURNAME_CONTROL_ID;
    }
    
    public static function getSurnameColumnName()
    {
        return self::SURNAME_COLUMN_NAME;
    }
    
    public static function getSurnameControlCaption()
    {
        return self::SURNAME_CONTROL_CAPTION;
    }
    /**
     * Set name
     *
     * @param string $Name
     * @return FoivPerson
     */
    public function setName($Name)
    {
        $this->name = $Name;

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
    
    public static function getNameControlID()
    {
        return self::NAME_CONTROL_ID;
    }
    
    public static function getNameColumnName()
    {
        return self::NAME_COLUMN_NAME;
    }
    
     public static function getNameControlCaption()
    {
        return self::NAME_CONTROL_CAPTION;
    }
    
     /**
     * Get patronymic
     *
     * @return string 
     */
    public function getPatronymic()
    {
        return $this->patronymic;
    }
    
     /**
     * Set patronymic 
     *
     * @param string $Patronymic
     * @return FoivPerson
     */
    public function setPatronymic($Patronymic)
    {
        $this->patronymic = $Patronymic;
        return $this;
    }
    
    public static function getPatronymicControlID()
    {
        return self::PATRONYMIC_CONTROL_ID;
    }
    
    public static function getPatronymicColumnName()
    {
        return self::PATRONYMIC_COLUMN_NAME;
    }
    
     public static function getPatronymicControlCaption()
    {
        return self::PATRONYMIC_CONTROL_CAPTION;
    }
    
	
	/**
     * Get ФИО
     *
     * @return string 
     */
    public function getFIO()
    {
        return $this->getSurname()." ".$this->getName()." ".$this->getPatronymic();    
    }
    
	public function setFIO($FIO)
	{
		if($FIO != null)
		{
			//разбиваем ФИО - строку на составные части
			$fio_array = explode(" ", $FIO);
			if($fio_array != null)
			{
				//вероятно указана только фамилия
				if(count($fio_array) >= 1 )
				{
					$this->surname = $fio_array[0];
				}
				
				//вероятно указана  фамилия и имя
				if(count($fio_array) >= 2 )
				{
					$this->name = $fio_array[1];
				}
				
				//указаны все составные части ФИО
				if(count($fio_array) == 3 )
				{
					$this->patronymic = $fio_array[2];
				}
			}
		}
	}
	
	/**
     * Get address
     *
     * @return string 
     */
	public function getPositionFIO()
	{
		return $this->getPosition()." ".$this->getFIO();
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
     * Set address
     *
     * @param string $Address
     * @return FoivPerson
     */
    public function setAddress($Address)
    {
        $this->address = $Address;
        return $this;
    }
    
     public static function getAddressControlID()
    {
        return self::ADDRESS_CONTROL_ID;
    }
    
    public static function getAddressColumnName()
    {
        return self::ADDRESS_COLUMN_NAME;
    }
  
    public static function getAddressControlCaption()
    {
        return self::ADDRESS_CONTROL_CAPTION;
    }
    
    /**
     * Get photo_file
     *
     * @return \NCUO\FoivBundle\Entity\File 
     */
    public function getPhotoFile()
    {
        return $this->photo_file;
    }
    
      /**
     * Set photo_file
     *
     * @param integer $PhotoID
     * @return FoivPerson
     */
    public function setPhotoFile(App\NCUO\FoivBundle\Entity\File $PhotoFile = null )
    {
        if($PhotoFile != null)
        {
            $this->photo_file = $PhotoFile;
        }
        
        return $this;
    }

    public function getPhotoFullPath()
    {
        if($this->photo_file != null)
        {
            return  $this->photo_file->getPath().$this->photo_file->getName();
        }
        
        return null;
    }
    
    public static function getPhotoFileControlID()
    {
        return self::PHOTO_FILE_CONTROL_ID;
    }
    
    public static function getPhotoFileColumnName()
    {
        return self::PHOTO_FILE_COLUMN_NAME;
    }
    
    public static function getPhotoFileControlCaption()
    {
        return self::PHOTO_FILE_CONTROL_CAPTION;
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
     * Set phone
     *
     * @param string $Phone
     * @return FoivPerson
     */
    public function setPhone($Phone)
    {
        $this->phone = $Phone;
        return $this;
    }
    
    public static function getPhoneControlID()
    {
        return self::PHONE_CONTROL_ID;
    }
    
    public static function getPhoneColumnName()
    {
        return self::PHONE_COLUMN_NAME;
    }
    
    public static function getPhoneControlCaption()
    {
        return self::PHONE_CONTROL_CAPTION;
    }
     /**
     * Get website_url
     *
     * @return string 
     */
    public function getWebsiteUrl()
    {
        return $this->website_url;
    }
    
     /**
     * Set website_url
     *
     * @param string $WebsiteUrl
     * @return FoivPerson
     */
    public function setWebsiteUrl($WebsiteUrl)
    {
        $this->website_url = $WebsiteUrl;
        return $this;
    }
    
    public static function getWebsiteUrlControlID()
    {
        return self::WEBSITE_URL_CONTROL_ID;
    }
    
    public static function getWebsiteUrlColumnName()
    {
        return self::WEBSITE_URL_COLUMN_NAME;
    }
        
    public static function getWebsiteUrlControlCaption()
    {
        return self::WEBSITE_URL_CONTROL_CAPTION;
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
     * Set email
     *
     * @param string $Email
     * @return FoivPerson
     */
    public function setEmail($Email)
    {
        $this->email = $Email;
        return $this;
    }
    
    public static function getEmailControlID()
    {
        return self::EMAIL_CONTROL_ID;
    }
    
    public static function getEmailColumnName()
    {
        return self::EMAIL_COLUMN_NAME;
    }
    
    public static function getEmailControlCaption()
    {
        return self::EMAIL_CONTROL_CAPTION;
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
     * Set position
     *
     * @param string $Position
     * @return FoivPerson
     */
    public function setPosition($Position)
    {
        $this->position = $Position;
        return $this;
    }
    
    public static function getPositionControlID()
    {
        return self::POSITION_CONTROL_ID;
    }
    
    public static function getPositionColumnName()
    {
        return self::POSITION_COLUMN_NAME;
    }
    
     public static function getPositionControlCaption()
    {
        return self::POSITION_CONTROL_CAPTION;
    }
      /**
     * Get biography
     *
     * @return string 
     */
    public function getBiography()
    {
        return $this->biography;
    }
    
     /**
     * Set biography
     *
     * @param string $Biography
     * @return FoivPerson
     */
    public function setBiography($Biography)
    {
        $this->biography = $Biography;
        return $this;
    }
    
    public static function getBiographyControlID()
    {
        return self::BIOGRAPHY_CONTROL_ID;
    }
    
    public static function getBiographyColumnName()
    {
        return self::BIOGRAPHY_COLUMN_NAME;
    }
   
    public static function getBiographyControlCaption()
    {
        return self::BIOGRAPHY_CONTROL_CAPTION;
    }
    
	public function getService()
	{
		return $this->service;
	}
	
	public function setService($Service)
	{
		$this->service = $Service;
		return $this;
	}
	
	public static function getServiceControlID()
    {
        return self::SERVICE_CONTROL_ID;
    }
    
    public static function getServiceColumnName()
    {
        return self::SERVICE_COLUMN_NAME;
    }
   
    public static function getServiceControlCaption()
    {
        return self::SERVICE_CONTROL_CAPTION;
    }
	
    public static function IsMandatory($FieldName)
    {
        if($FieldName == null )
        {
            throw new \Exception('Не задано имя столбца таблицы dict_person');
        }
        
         switch($FieldName)
        {
            case self::SURNAME_COLUMN_NAME: return true;
            case self::NAME_COLUMN_NAME: return true;    
            case self::PATRONYMIC_COLUMN_NAME: return true;
            case self::ADDRESS_COLUMN_NAME: return false;
            case self::BIOGRAPHY_COLUMN_NAME: return false;
            case self::EMAIL_COLUMN_NAME: return false;
            case self::PHONE_COLUMN_NAME: return false;
            case self::PHOTO_FILE_COLUMN_NAME: return false;
            case self::POSITION_COLUMN_NAME: return false;
            case self::WEBSITE_URL_COLUMN_NAME: return false;
			case self::SERVICE_COLUMN_NAME: return false;

            default:  throw new \Exception('Не найдено имя столбца "'.$FieldName.'" таблицы dict_person');
        }
     
    }
     
    public function getAsArray()
    {
        return array("id"=>$this->getId(),
                               "surname" => $this->getSurname(),
                               "name" => $this->getName(),
                               "patronymic" => $this->getPatronymic(),
							   "address" => $this->getAddress(),
                               "phone" => $this->getPhone(),
                               "web_site" => $this->getWebsiteUrl(),
                               "biography" => $this->getBiography(),
                               "position" => $this->getPosition(),
                               "email" => $this->getEmail(),
							   "service" => $this->getService()
                               );
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

?>