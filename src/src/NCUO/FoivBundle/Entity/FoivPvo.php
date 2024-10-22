<?php

namespace App\NCUO\FoivBundle\Entity;

use App\NCUO\FoivBundle\Entity\ORMObjectMetadata;
use Doctrine\ORM\Mapping as ORM;

/**
 * FoivPvo
 *
 * @ORM\Table(name="eif_data2.dict_foiv_pvo", indexes={@ORM\Index(name="IDX_91E540EA8CDE5729", columns={"type"}), @ORM\Index(name="IDX_91E540EAFDE52107", columns={"fk_foiv"})})
 * @ORM\Entity
 */
class FoivPvo
{
    
    const FULL_NAME_FIELD_NAME = "Полное наименование";
    const FULL_NAME_CONTROL_ID = "FULL_NAME";
    const FULL_NAME_CONTROL_TYPE = "editbox";
    const FULL_NAME_COLUMN_NAME = "name";
    
    const SHORT_NAME_FIELD_NAME = "Сокращенное наименование";
    const SHORT_NAME_CONTROL_ID = "SHORT_NAME";
    const SHORT_NAME_CONTROL_TYPE = "editbox";
    const SHORT_NAME_COLUMN_NAME = "shortName";
    
    const WEB_URL_FIELD_NAME = "Веб-сайт";
    const WEB_URL_CONTROL_ID = "WEB_URL";
    const WEB_URL_CONTROL_TYPE = "textarea";
    const WEB_URL_COLUMN_NAME = "websiteUrl";
    
    const ADDRESS_FIELD_NAME = "Адрес";
    const ADDRESS_CONTROL_ID = "ADDRESS";
    const ADDRESS_CONTROL_TYPE = "textarea";
    const ADDRESS_COLUMN_NAME = "address";
    
    const PHONE_FIELD_NAME = "Контактные номера";
    const PHONE_CONTROL_ID = "PHONE";
    const PHONE_CONTROL_TYPE = "editbox";
    const PHONE_COLUMN_NAME = "address";
    
    const EMAIL_FIELD_NAME = "Электронная почта";
    const EMAIL_CONTROL_ID = "EMAIL";
    const EMAIL_CONTROL_TYPE = "editbox";
    const EMAIL_COLUMN_NAME = "email";
    
    const FUNCTIONS_FIELD_NAME = "Основные функции";
    const FUNCTIONS_CONTROL_ID = "FUNCTIONS";
    const FUNCTIONS_CONTROL_TYPE = "editbox";
    const FUNCTIONS_COLUMN_NAME = "functions";
    
    const DIRECTOR_FIELD_NAME = "Руководитель";
    const DIRECTOR_CONTROL_ID = "DIRECTOR";
    const DIRECTOR_CONTROL_TYPE = "combobox";
    const DIRECTOR_COLUMN_NAME = "director";
    
    const TYPE_FIELD_NAME = "Тип организации";
    const TYPE_CONTROL_ID = "TYPE";
    const TYPE_CONTROL_TYPE = "combobox";
    const TYPE_COLUMN_NAME = "type";
    
    const FOIV_FIELD_NAME = "ФОИВ - паспорт";
    const FOIV_CONTROL_ID = "FOIV";
    const FOIV_CONTROL_TYPE = "combobox";
    const FOIV_COLUMN_NAME = "foiv";
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="eif_data2.seq_foiv_pvo", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
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
     * @ORM\Column(name="website_url", type="text", nullable=true)
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
     * @ORM\Column(name="functions", type="text", nullable=true)
     */
    private $functions;

    
    /**
     * @var App\NCUO\FoivBundle\Entity\FoivPvoPersons
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\FoivPvoPersons")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="director", referencedColumnName="id")
     * })
     */
    private $director;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="text", nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var App\NCUO\FoivBundle\Entity\FoivPvoTypes
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\FoivPvoTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type", referencedColumnName="id")
     * })
     */
    private $type;

    /**
     * @var App\NCUO\FoivBundle\Entity\Foiv
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\Foiv")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fk_foiv", referencedColumnName="id")
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
     * @return FoivPvo
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

    public static function getFullNameControlID()
    {
        return self::FULL_NAME_CONTROL_ID;
    }
    
    public static function getFullNameControlName()
    {
        return self::FULL_NAME_COLUMN_NAME;
    }
    
     public static function getFullNameControlCaption()
    {
        return self::FULL_NAME_FIELD_NAME;    
    }
    /**
     * Set shortName
     *
     * @param string $shortName
     * @return FoivPvo
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

    public static function getShortNameControlID()
    {
        return self::SHORT_NAME_CONTROL_ID;
    }
    
    public static function getShortNameControlName()
    {
        return self::SHORT_NAME_COLUMN_NAME;
    }
    
    public static function getShortNameControlCaption()
    {
        return self::SHORT_NAME_FIELD_NAME;    
    }
    /**
     * Set websiteUrl
     *
     * @param string $websiteUrl
     * @return FoivPvo
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

    public static function getWebURLControlID()
    {
        return self::WEB_URL_CONTROL_ID;
    }
    
    public static function getWebURLControlName()
    {
        return self::WEB_URL_COLUMN_NAME;
    }
    
    public static function getWebURLControlCaption()
    {
        return self::WEB_URL_FIELD_NAME;    
    }
    /**
     * Set address
     *
     * @param string $address
     * @return FoivPvo
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

    public static function getAddressControlID()
    {
        return self::ADDRESS_CONTROL_ID;
    }
    
     public static function getAddressControlName()
    {
        return self::ADDRESS_COLUMN_NAME;
    }
    
    public static function getAddressControlCaption()
    {
        return self::ADDRESS_FIELD_NAME;    
    }
    /**
     * Set functions
     *
     * @param string $functions
     * @return FoivPvo
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

    public static function getFunctionsControlID()
    {
        return self::FUNCTIONS_CONTROL_ID;
    }
    
    public static function getFunctionsControlName()
    {
        return self::FUNCTIONS_COLUMN_NAME;
    }
    
    public static function getFunctionsControlCaption()
    {
        return self::FUNCTIONS_FIELD_NAME;    
    }
    /**
     * Set director
     *
     * @param integer $director
     * @return FoivPvo
     */
    public function setDirector($director)
    {
        $this->director = $director;

        return $this;
    }

    /**
     * Get director
     *
     * @return integer 
     */
    public function getDirector()
    {
        return $this->director;
    }

    public static function getDirectorControlID()
    {
        return  self::DIRECTOR_CONTROL_ID ;   
    }
    
    public static function getDirectorControlName()
    {
        return self::DIRECTOR_COLUMN_NAME;
    }
    
    public static function getDirectorControlCaption()
    {
        return self::DIRECTOR_FIELD_NAME;    
    }
    /**
     * Set phone
     *
     * @param string $phone
     * @return FoivPvo
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

    public static function getPhoneControlID()
    {
        return self::PHONE_CONTROL_ID;
    }
    
    public static function getPhoneControlName()
    {
        return self::PHONE_COLUMN_NAME;
    }
    
    public static function getPhoneControlCaption()
    {
        return self::PHONE_FIELD_NAME;    
    }
    
    /**
     * Set email
     *
     * @param string $email
     * @return FoivPvo
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

    public static function getEmailControlID()
    {
        return self::EMAIL_CONTROL_ID;
    }
    
    public static function getEmailControlName()
    {
        return self::EMAIL_COLUMN_NAME;
    }
    
    public static function getEmailControlCaption()
    {
        return self::EMAIL_FIELD_NAME;    
    }
    
    /**
     * Set type
     *
     * @param \NCUO\FoivBundle\Entity\FoivPvoTypes $type
     * @return FoivPvo
     */
    public function setType(App\NCUO\FoivBundle\Entity\FoivPvoTypes $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \NCUO\FoivBundle\Entity\FoivPvoTypes 
     */
    public function getType()
    {
        return $this->type;
    }

    public static function getTypeControlID()
    {
        return  self::TYPE_CONTROL_ID ;   
    }
    
    public static function getTypeControlName()
    {
        return self::TYPE_COLUMN_NAME;
    }
    
    public static function getTypeControlCaption()
    {
        return self::TYPE_FIELD_NAME;    
    }
    
    /**
     * Set foiv
     *
     * @param \NCUO\FoivBundle\Entity\Foiv $foiv
     * @return FoivPvo
     */
    public function setFoiv(App\NCUO\FoivBundle\Entity\Foiv $foiv = null)
    {
        $this->foiv = $foiv;

        return $this;
    }

    /**
     * Get foiv
     *
     * @return \NCUO\FoivBundle\Entity\Foiv 
     */
    public function getFoiv()
    {
        return $this->foiv;
    }
    
    public static function getFoivControlID()
    {
        return  self::FOIV_CONTROL_ID ;   
    }
    
    public static function getFoivControlName()
    {
        return self::FOIV_COLUMN_NAME;
    }
    
    public static function getFoivControlCaption()
    {
        return self::FOIV_FIELD_NAME;    
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
    
    /*
     *Функция получения списка полей
     *Данная функция генерирует ассоциативный массив по каждому полю, то есть для каждого HTML-контрола
     * Порядок данных в массив таков:
     * ключ массива: ID HTML-контрола 
     * Поле №1: тип контрола (editbox, combobox, textarea или updown )
     * Поле №2 : заголовок к данному контролу
     * Поле №3: признак того, что  данный контрол обязателен для заполнения или выбора данных
     *    
     * $OrmMetaData - объект метаинформации об конкретном классе, отображаемого из Doctrine. В нашем случает это FoivPvo.
     *                                 Используется для выявления обязательных или необязательных полей
     */
    public static function getFieldNames($OrmMetaData)
    {
        return Array( self::FULL_NAME_CONTROL_ID         => array( self::FULL_NAME_CONTROL_TYPE,     self::FULL_NAME_FIELD_NAME),
                                self::SHORT_NAME_CONTROL_ID     => array( self::SHORT_NAME_CONTROL_TYPE,     self::SHORT_NAME_FIELD_NAME),
                                self::WEB_URL_CONTROL_ID             => array( self::WEB_URL_CONTROL_TYPE,    self::WEB_URL_FIELD_NAME),
                                self::ADDRESS_CONTROL_ID              => array( self::ADDRESS_CONTROL_TYPE, self::ADDRESS_FIELD_NAME),
                                self::FUNCTIONS_CONTROL_ID          => array( self::FUNCTIONS_CONTROL_TYPE,      self::FUNCTIONS_FIELD_NAME),
                                self::DIRECTOR_CONTROL_ID             => array( self::DIRECTOR_CONTROL_TYPE,      self::DIRECTOR_FIELD_NAME),
                                self::PHONE_CONTROL_ID                  => array( self::PHONE_CONTROL_TYPE,      self::PHONE_FIELD_NAME),
                                self::EMAIL_CONTROL_ID                    => array( self::EMAIL_CONTROL_TYPE,      self::EMAIL_FIELD_NAME),
                                self::TYPE_CONTROL_ID                       => array( self::TYPE_CONTROL_TYPE,      self::TYPE_FIELD_NAME)
                             );
                                
    }
    
      /*
     *Функция получения списка полей c введенными данными
     *Данная функция генерирует ассоциативный массив по каждому полю, то есть для каждого HTML-контрола
     * Порядок данных в массив таков:
     * ключ массива: ID HTML-контрола 
     * Поле №1: тип контрола (editbox, combobox, textarea или updown )
     * Поле №2 : заголовок к данному контролу
     * Поле №3: данные для данного поля
     * Поле №4: признак того, что  данный контрол обязателен для заполнения или выбора данных
     
     * FoivPvo - Объект , содержащий искомые данные
     * OrmMetaData - объект метаинформации об конкретном классе, отображаемого из Doctrine. В нашем случает это FoivPvo.
     *                              Используется для выявления обязательных или необязательных полей
     */
    public static function getFieldNamesWithData($FoivPvo, $OrmMetaData)
    {
         return Array( self::FULL_NAME_CONTROL_ID         => array( self::FULL_NAME_CONTROL_TYPE,     self::FULL_NAME_FIELD_NAME, $FoivPvo->getName()),
                                self::SHORT_NAME_CONTROL_ID     => array( self::SHORT_NAME_CONTROL_TYPE,     self::SHORT_NAME_FIELD_NAME, $FoivPvo->getShortName()),
                                self::WEB_URL_CONTROL_ID             => array( self::WEB_URL_CONTROL_TYPE,    self::WEB_URL_FIELD_NAME, $FoivPvo->getWebsiteUrl()),
                                self::ADDRESS_CONTROL_ID              => array( self::ADDRESS_CONTROL_TYPE, self::ADDRESS_FIELD_NAME, $FoivPvo->getAddress()), 
                                self::FUNCTIONS_CONTROL_ID          => array( self::FUNCTIONS_CONTROL_TYPE,      self::FUNCTIONS_FIELD_NAME, $FoivPvo->getFunctions()),
                                self::DIRECTOR_CONTROL_ID             => array( self::DIRECTOR_CONTROL_TYPE,      self::DIRECTOR_FIELD_NAME, $FoivPvo->getDirector()),
                                self::PHONE_CONTROL_ID                  => array( self::PHONE_CONTROL_TYPE,      self::PHONE_FIELD_NAME, $FoivPvo->getPhone()),
                                self::EMAIL_CONTROL_ID                    => array( self::EMAIL_CONTROL_TYPE,      self::EMAIL_FIELD_NAME, $FoivPvo->getEmail()),
                                self::TYPE_CONTROL_ID                       => array( self::TYPE_CONTROL_TYPE,      self::TYPE_FIELD_NAME, $FoivPvo->getType())
                                     );
    }
}
