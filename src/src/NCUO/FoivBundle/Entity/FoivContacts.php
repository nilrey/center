<?php

namespace App\NCUO\FoivBundle\Entity;

use App\NCUO\FoivBundle\Entity\ORMObjectMetadata;
use Doctrine\ORM\Mapping as ORM;

use App\NCUO\FoivBundle\Entity\FoivPerson;

/**
 * FoivContacts 
 * 
 
 * @ORM\Table(name="eif_data2.dict_foiv_contacts", indexes={@ORM\Index(name="IDX_7409DA5ACFDEED70", columns={"foiv_id"})})
 * @ORM\Entity(repositoryClass="NCUO\FoivBundle\Entity\FoivContactRepository")
  */
class FoivContacts
{
    const NAME_FIELD_NAME = "Наименование контакта";
    const NAME_CONTROL_ID = "CONTACT";
    const NAME_CONTROL_TYPE = "editbox";
     const NAME_COLUMN_NAME = "name";
    
    const PHONE_FIELD_NAME = "Телефоны";
    const PHONE_CONTROL_ID = "PHONE";
    const PHONE_CONTROL_TYPE = "editbox";
    const PHONE_COLUMN_NAME = "phoneNumbers";
    
    const EMAIL_FIELD_NAME = "Электронная почта";
    const EMAIL_CONTROL_ID = "EMAIL";
    const EMAIL_CONTROL_TYPE = "editbox";
    const EMAIL_COLUMN_NAME = "email";
    
    const ADDRESS_FIELD_NAME = "Почтовый адрес";
    const ADDRESS_CONTROL_ID = "ADDRESS";
    const ADDRESS_CONTROL_TYPE = "editbox";
    const ADDRESS_COLUMN_NAME = "address";
    
    const PERSON_CONTROL_ID = "FIO";
    const PERSON_COLUMN_NAME = "person_id";
    const PERSON_FIELD_CAPTION = "ФИО";
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="eif_data2.seq_foiv_contacts", allocationSize=1, initialValue=1)
     */
    private $id;
    
    /**
     * @var App\NCUO\FoivBundle\Entity\Foiv
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\Foiv")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="foiv_id", referencedColumnName="id")
     * })
     */
    private $foiv;

    /**
     * @var App\NCUO\FoivBundle\Entity\FoivPerson
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\FoivPerson")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     * })
     */
     private $person;
	 
	 ////////////////////////////////////////////////////
	 // Для совместивости старые поля временно оставлены.
	 // Позже данные старые поля будут удалены из PHP-кода 
	 // и из таблицы dict_foiv_contacts
	 ////////////////////////////////////////////////////
	  /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;
    

    /**
     * @var string
     *
     * @ORM\Column(name="phone_numbers", type="string", length=255, nullable=false)
     */
    private $phoneNumbers;
    

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     */
    private $address;

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
     * @return FoivContacts
     */
    public function setName($name)
    {
       $this->person->setName($name);
	   
	   //временно используем 
	   $this->name = $this->getFIO()." ".$thus->person->getPosition();
       return $this;
    }
    
    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
       
	   return $this->person->getFIO();
    }
   
    
    public static function getNameControlID()
    {
        return self::NAME_CONTROL_ID;
    }
    
    public static function getNameControlName()
    {
        return self::NAME_COLUMN_NAME;
    }
    
    public function getFIO()
    {
        return $this->person->getSurname()." ".$this->person->getName()." ".$this->person->getPatronymic();
    }
    /**
     * Set phone
     *
     * @param string $Phone
     * @return FoivContacts
     */
    public function setPhone($Phone)
    {
		//временно используем 
	    $this->phoneNumbers = $Phone;
        $this->person->setPhone($Phone);
        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
		if($this->person == null)
		{
			return null;
		}
		
        return $this->person->getPhone();
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
     * Set Email
     *
     * @param string $email
     * @return FoivContacts
     */
    public function setEmail($email)
    {
		//временно используем 
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
		if($this->person == null)
		{
			return null;
		}
       return $this->person->getEmail();
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
     * Set address
     *
     * @param string $Address
     * @return FoivContacts
     */
    public function setAddress($address)
    {
		//временно используем 
		$this->$address = $address;
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
        if($this->person == null)
		{
			return null;
		}
        return $this->person->getAddress();
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
     * Set foiv
     *
     * @param App\NCUO\FoivBundle\Entity\Foiv $foiv
     * @return FoivContacts
     */
    public function setFoiv(App\NCUO\FoivBundle\Entity\Foiv $foiv = null)
    {
        $this->foiv = $foiv;

        return $this;
    }

    /**
     * Get foiv
     *
     * @return App\NCUO\FoivBundle\Entity\Foiv 
     */
    public function getFoiv()
    {
        return $this->foiv;
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
     * @param App\NCUO\FoivBundle\Entity\FoivPerson $Person
     * @return FoivContacts
     */
    public function setPerson(App\NCUO\FoivBundle\Entity\FoivPerson $Person)
    {
         $this->person = $Person;
		 //временно фиксируем по старым полям
		 $this->name 		 = $this->person->getPosition()." ".$this->person->getFIO();
		 $this->phoneNumbers = $this->person->getPhone();
		 $this->email 		 = $this->person->getEmail();
		 $this->address 	 = $this->person->getAddress();
         return $this;
    }
    
	public function getAsArray()
	{
		 return array("id"=>$this->getId(),
					  "surname" => $this->person->getSurname(),
                      "name" => $this->person->getName(),
					  "patronymic" => $this->person->getPatronymic(),
                      "phone" => $this->person->getPhone(),
                      "email" => $this->person->getEmail(),
                      "address" => $this->person->getAddress(),
                      "position" => $this->person->getPosition(),
					  "service" => $this->person->getService()
                     );
	}
	
    public static function getPersonControlName()
    {
       return self::PERSON_COLUMN_NAME;
    }
    
    public static function getPersonControlID()
    {
        return self::PERSON_CONTROL_ID;
    }
    
    public static function getPersonControlCaption()
    {
        return self::PERSON_FIELD_CAPTION;
    }
	
	/*
	* Проверка поля  на обязательное заполнение
	* $FieldName - имя столбца таблицы 
	*
	* Возврат - результат проверки (TRUE - данное поле обязательно к заполнению / FALSE - данное поле необязательное)
	* Примечание: 1) Если нужного столбца таблицы не найдено - генерируется исключение
	*			  2) Т.к. сущность FoivContacts связана с сущностью FoivPerson, 
	*				 то используются проверочные функции сущности FoivPerson
	*/
	public static function IsMandatory($FieldName)
    {
        if($FieldName == null )
        {
            throw new \Exception('Не задано имя столбца таблицы dict_person');
        }
        
		
        switch($FieldName)
        {
            case FoivPerson::getSurnameColumnName(): 
            case FoivPerson::getNameColumnName(): 	 
            case FoivPerson::getPatronymicColumnName(): 
            case FoivPerson::getAddressColumnName(): 
            case FoivPerson::getEmailColumnName(): 
            case FoivPerson::getPhoneColumnName(): return FoivPerson::IsMandatory($FieldName);

            default:  throw new \Exception('Не найдено имя столбца "'.$FieldName.'" связанной таблицы dict_person');
        }
     
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
     * $OrmMetaData - объект метаинформации об конкретном классе, отображаемого из Doctrine. В нашем случает это FoivContacts.
     *                                 Используется для выявления обязательных или необязательных полей
     */
    /*public static function getFieldNames($OrmMetaData)
    {
            return Array( FoivContacts::getNameControlID()     => array( self::NAME_CONTROL_TYPE,     FoivContacts::getNameControlCaption()),
                                    FoivContacts::getPhoneControlID()    => array( self::PHONE_CONTROL_TYPE,    FoivContacts::getPhoneControlCaption()),
                                    FoivContacts::getEmailControlID()     => array( self::EMAIL_CONTROL_TYPE,      FoivContacts::getEmailControlCaption()),
                                    FoivContacts::getAddressControlID() => array( self::ADDRESS_CONTROL_TYPE, FoivContacts::getAddressControlCaption()));
    }*/
    
    
     /*
     *Функция получения списка полей c введенными данными
     *Данная функция генерирует ассоциативный массив по каждому полю, то есть для каждого HTML-контрола
     * Порядок данных в массив таков:
     * ключ массива: ID HTML-контрола 
     * Поле №1: тип контрола (editbox, combobox, textarea или updown )
     * Поле №2 : заголовок к данному контролу
     * Поле №3: данные для данного поля
     * Поле №4: признак того, что  данный контрол обязателен для заполнения или выбора данных
     
     * FoivContact - Объект , содержащий искомые данные
     * OrmMetaData - объект метаинформации об конкретном классе, отображаемого из Doctrine. В нашем случает это FoivContacts.
     *                              Используется для выявления обязательных или необязательных полей
     */
    /*public static function getFieldNamesWithData($FoivContact, $OrmMetaData)
    {
            return Array( FoivContacts::getNameControlID()     => array(self::NAME_CONTROL_TYPE,      self::NAME_FIELD_NAME,      $FoivContact->getName()),
                                    FoivContacts::getPhoneControlID()    => array(self::PHONE_CONTROL_TYPE,     self::PHONE_FIELD_NAME,    $FoivContact->getPhoneNumbers()),
                                    FoivContacts::getEmailControlID()      => array(self::EMAIL_CONTROL_TYPE,      self::EMAIL_FIELD_NAME,      $FoivContact->getEmail()),
                                    FoivContacts::getAddressControlID() =>  array(self::ADDRESS_CONTROL_TYPE, self::ADDRESS_FIELD_NAME, $FoivContact->getAddress()));
    }*/
}
