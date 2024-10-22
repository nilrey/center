<?php

namespace App\NCUO\FoivBundle\Entity;

use App\NCUO\FoivBundle\Entity\ORMObjectMetadata;
use Doctrine\ORM\Mapping as ORM;


/**
 * Foiv
 *
 * @ORM\Table(name="eif_data.dict_foiv", indexes={@ORM\Index(name="fki_address_fk", columns={"address_fk"}), @ORM\Index(name="fki_reglament_fk", columns={"reglament_fk"}), @ORM\Index(name="IDX_6934BFB41E90D3F0", columns={"director"}), @ORM\Index(name="IDX_6934BFB4AC4BA902", columns={"superfoiv"})})
 * @ORM\Entity(repositoryClass="FoivRepository")
 */
class Foiv
{
    
    const FULL_NAME_FIELD_NAME = "Полное наименование";
    const FULL_NAME_COLUMN_NAME = "name";
    const FULL_NAME_CONTROL_ID = "FULL_NAME";
    const FULL_NAME_CONTROL_TYPE = "editbox";
    
    
    const SHORT_NAME_FIELD_NAME = "Сокращенное наименование";
    const SHORT_NAME_CONTROL_ID = "SHORT_NAME";
    const SHORT_NAME_COLUMN_NAME = "shortname";
    const SHORT_NAME_CONTROL_TYPE = "editbox";
    
    const SUPERFOIV_FIELD_NAME = "Руководящий орган";
    const SUPERFOIV_CONTROL_ID = "SUPERFOIV_NAME";
    const SUPERFOIV_COLUMN_NAME = "superfoiv";
    const SUPERFOIV_CONTROL_TYPE = "combobox";
    
    const SITENAME_FIELD_NAME = "Заголовк официального сайта";
    const SITENAME_CONTROL_ID = "SITENAME_NAME";
    const SITENAME_COLUMN_NAME = "sitename";
    const SITENAME_CONTROL_TYPE = "editbox";
    
    const SITEURL_FIELD_NAME = "URL-ссылка официального сайта";
    const SITEURL_CONTROL_ID = "SITEURL_NAME";
    const SITEURL_COLUMN_NAME = "siteurl";
    const SITEURL_CONTROL_TYPE = "editbox";
    
    const TYPE_FIELD_NAME = "Категория";
    const TYPE_CONTROL_ID = "TYPE_NAME";
    const TYPE_COLUMN_NAME = "type";
    const TYPE_CONTROL_TYPE = "editbox";
    
    const DIRECTOR_FIELD_NAME = "Руководитель";
    const DIRECTOR_CONTROL_ID = "DIRECTOR_NAME";
    const DIRECTOR_COLUMN_NAME = "director";
    const DIRECTOR_CONTROL_TYPE = "combobox";
    
    const STATELINK_FIELD_NAME = "Положение по ФОИВ";
    const STATELINK_CONTROL_ID = "STATELINK_NAME";
    const STATELINK_COLUMN_NAME = "state_link";
    const STATELINK_CONTROL_TYPE = "editbox";
    
    const DESCRIPTION_FIELD_NAME = "Назначение";
    const DESCRIPTION_CONTROL_ID = "DESCRIPTION_NAME";
    const DESCRIPTION_COLUMN_NAME = "description_text";
    const DESCRIPTION_CONTROL_TYPE = "textarea";
    
    const ADDRESS_FIELD_NAME = "Адрес";
    const ADDRESS_CONTROL_ID = "ADDRESS_NAME";
    const ADDRESS_COLUMN_NAME = "address_fk";
    const ADDRESS_CONTROL_TYPE = "combobox";
    
    const VERSION_FIELD_NAME = "Версия";
    const VERSION_CONTROL_ID = "VERSION_NAME";
    const VERSION_COLUMN_NAME = "version";
    const VERSION_CONTROL_TYPE = "updown";
    
    const ICONSTYLE_FIELD_NAME = "Стиль иконки";
    const ICONSTYLE_CONTROL_ID = "ICONSTYLE_NAME";
    const ICONSTYLE_COLUMN_NAME = "iconstyle";
    const ICONSTYLE_CONTROL_TYPE = "editbox";
    
    const SORTORDER_FIELD_NAME = "Порядок сортировки";
    const SORTORDER_CONTROL_ID = "SORTORDER_NAME";
    const SORTORDER_COLUMN_NAME = "sort_order";
    const SORTORDER_CONTROL_TYPE = "updown";
    
    const BASICTASKS_FIELD_NAME = "Основные задачи";
    const BASICTASKS_CONTROL_ID = "BASICTASKS_NAME";
    const BASICTASKS_COLUMN_NAME = "basictasks";
    const BASICTASKS_CONTROL_TYPE = "textarea";
    
    const SUBSYSTEMS_FIELD_NAME = "Подсистемы";
    const SUBSYSTEMS_CONTROL_ID = "SUBSYSTEMS_NAME";
    const SUBSYSTEMS_COLUMN_NAME = "subsystems";
    const SUBSYSTEMS_CONTROL_TYPE = "textarea";
    
    const CONVENTIONS_FIELD_NAME = "Соглашения";
    const CONVENTIONS_CONTROL_ID = "CONVENTIONS_NAME";
    const CONVENTIONS_COLUMN_NAME = "conventions";
    const CONVENTIONS_CONTROL_TYPE = "textarea";
    
    const MAPURL_FIELD_NAME = "URL-ссылка на карты";
    const MAPURL_CONTROL_ID = "MAPURL_NAME";
    const MAPURL_COLUMN_NAME = "mapurl";
    const MAPURL_CONTROL_TYPE = "editbox";
    
    const LOCALSITE_FIELD_NAME = "URL-ссылка локального сайта";
    const LOCALSITE_CONTROL_ID = "LOCALSITE_NAME";
    const LOCALSITE_COLUMN_NAME = "local_site";
    const LOCALSITE_CONTROL_TYPE = "editbox";
    
    const ENGAGED_FIELD_NAME = "Занятость";
    const ENGAGED_CONTROL_ID = "ENGAGED_NAME";
    const ENGAGED_COLUMN_NAME = "engaged";
    const ENGAGED_CONTROL_TYPE = "updown";
    
    const REGLAMENT_FIELD_NAME = "Регламент";
    const REGLAMENT_CONTROL_ID = "REGLAMENT_NAME";
    const REGLAMENT_COLUMN_NAME = "reglament_fk";
    const REGLAMENT_CONTROL_TYPE = "combobox";
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="eif_data2.dict_foiv", allocationSize=1, initialValue=1)
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
     * @ORM\Column(name="shortname", type="string", length=255, nullable=true)
     */
    private $shortname;

    /**
     * @var string
     *
     * @ORM\Column(name="siteurl", type="string", length=95, nullable=true)
     */
    private $siteurl;

    /**
     * @var string
     *
     * @ORM\Column(name="sitename", type="string", length=95, nullable=true)
     */
    private $sitename;

    /**
     * @var integer
     *
     * @ORM\Column(name="version", type="integer", nullable=true)
     */
    private $version;

    /**
     * @var string
     *
     * @ORM\Column(name="iconstyle", type="string", length=255, nullable=true)
     */
    private $iconstyle;

    /**
     * @var integer
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=true)
     */
    private $sortOrder;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="basictasks", type="text", nullable=true)
     */
    private $basictasks;

    /**
     * @var string
     *
     * @ORM\Column(name="subsystems", type="text", nullable=true)
     */
    private $subsystems;

    /**
     * @var string
     *
     * @ORM\Column(name="conventions", type="text", nullable=true)
     */
    private $conventions;

    /**
     * @var string
     *
     * @ORM\Column(name="mapurl", type="string", nullable=true)
     */
    private $mapurl;

    /**
     * @var string
     *
     * @ORM\Column(name="local_site", type="string", nullable=true)
     */
    private $localSite;

    /**
     * @var integer
     *
     * @ORM\Column(name="engaged", type="integer", nullable=true)
     */
    private $engaged = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="description_text", type="string", nullable=true)
     */
    private $descriptionText;

    /**
     * @var string
     *
     * @ORM\Column(name="state_link", type="string", nullable=true)
     */
    private $stateLink;

    /**
     * @var App\NCUO\FoivBundle\Entity\FoivPersons
     *
     * @ORM\OneToMany(targetEntity="App\NCUO\FoivBundle\Entity\FoivPersons", mappedBy="fkFoiv")
     */
    private $foiv_persons;

    /**
     * @var App\NCUO\FoivBundle\Entity\FoivDepartments
     *
     * @ORM\OneToMany(targetEntity="App\NCUO\FoivBundle\Entity\FoivDepartments", mappedBy="foiv")
     */
    private $foiv_departments;

    /**
     * @var App\NCUO\FoivBundle\Entity\Address
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\Address")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="address_fk", referencedColumnName="id")
     * })
     */
    private $address;

    /**
     * @var App\NCUO\FoivBundle\Entity\FoivReglament
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\FoivReglament")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="reglament_fk", referencedColumnName="id")
     * })
     */
    private $reglament;

    /**
     * @var App\NCUO\FoivBundle\Entity\Foiv
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\Foiv")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="superfoiv", referencedColumnName="id")
     * })
     */
    private $superfoiv;

    /**
     * @var App\NCUO\FoivBundle\Entity\Foiv
     *
     * @ORM\OneToMany(targetEntity="App\NCUO\FoivBundle\Entity\Foiv", mappedBy="superfoiv")
     */
    private $foiv_children;

    /**
     * @ORM\OneToMany(targetEntity="App\NCUO\EifBundle\Entity\Source", mappedBy="foiv", fetch="EXTRA_LAZY")
     */
    
    private $sources;
    
    /*
     * @ORM\OneToMany(targetEntity="App\NCUO\NsiBundle\Entity\Owner", mappedBy="foiv", fetch="EXTRA_LAZY")
    
    private $owners;    
     */

    
    
    
    
    
    
      /**
     * @var App\NCUO\FoivBundle\Entity\Foiv
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\FoivPersons")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="director", referencedColumnName="id")
     * })
     */
    private $director;
    
    
    

    
    
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
     * Constructor
     */
    public function __construct()
    {
        $this->foiv_persons = new \Doctrine\Common\Collections\ArrayCollection();
        $this->sources = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }
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
     * @return Foiv
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
    
     public static function getFullNameColumnName()
    {
        return self::FULL_NAME_COLUMN_NAME;
    }
    
    public static function getFullNameControlCaption()
    {
        return self::FULL_NAME_FIELD_NAME;    
    }
   
    /**
     * Set shortname
     *
     * @param string $shortname
     * @return Foiv
     */
    public function setShortname($shortname)
    {
        $this->shortname = $shortname;

        return $this;
    }

    /**
     * Get shortname
     *
     * @return string 
     */
    public function getShortname()
    {
        return $this->shortname;
    }

    public static function getShortNameControlID()
    {
        return self::SHORT_NAME_CONTROL_ID;
    }
    
     public static function getShortNameColumnName()
    {
        return self::SHORT_NAME_COLUMN_NAME;
    }
    
     public static function getShortNameControlCaption()
    {
        return self::SHORT_NAME_FIELD_NAME;    
    }
    /**
     * Set siteurl
     *
     * @param string $siteurl
     * @return Foiv
     */
    public function setSiteurl($siteurl)
    {
        $this->siteurl = $siteurl;

        return $this;
    }

    /**
     * Get siteurl
     *
     * @return string 
     */
    public function getSiteurl()
    {
        return $this->siteurl;
    }

    public static function getSiteURLControlID()
    {
        return self::SITEURL_CONTROL_ID;
    }
    
     public static function getSiteURLColumnName()
    {
        return self::SITEURL_COLUMN_NAME;
    }
    
     public static function getSiteURLControlCaption()
    {
        return self::SITEURL_FIELD_NAME;    
    }
    /**
     * Set sitename
     *
     * @param string $sitename
     * @return Foiv
     */
    public function setSitename($sitename)
    {
        $this->sitename = $sitename;

        return $this;
    }

    /**
     * Get sitename
     *
     * @return string 
     */
    public function getSitename()
    {
        return $this->sitename;
    }

    public static function getSiteNameControlID()
    {
        return self::SITENAME_CONTROL_ID;
    }
    
    public static function getSiteNameColumnName()
    {
        return self::SITENAME_COLUMN_NAME;
    }
    
    public static function getSiteNameControlCaption()
    {
        return self::SITENAME_FIELD_NAME;    
    }
    /**
     * Set version
     *
     * @param integer $version
     * @return Foiv
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @return integer 
     */
    public function getVersion()
    {
        return $this->version;
    }

    public function getVersionControlID()
    {
        return self::VERSION_CONTROL_ID;
    }
    
    public static function getVersionColumnName()
    {
        return self::VERSION_COLUMN_NAME;
    }
    
    public static function getVersionControlCaption()
    {
        return self::VERSION_FIELD_NAME;    
    }
    
    /**
     * Set iconstyle
     *
     * @param string $iconstyle
     * @return Foiv
     */
    public function setIconstyle($iconstyle)
    {
        $this->iconstyle = $iconstyle;

        return $this;
    }

    /**
     * Get iconstyle
     *
     * @return string 
     */
    public function getIconstyle()
    {
        return $this->iconstyle;
    }

    public static function getIconStyleControlID()
    {
        return self::ICONSTYLE_CONTROL_ID;    
    }
    
    public static function getIconStyleColumnName()
    {
        return self::ICONSTYLE_COLUMN_NAME;
    }
    
    public static function getIconStyleControlCaption()
    {
        return self::ICONSTYLE_FIELD_NAME;    
    }
    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     * @return Foiv
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * Get sortOrder
     *
     * @return integer 
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

     public static function getSortOrderControlID()
     {
        return self::SORTORDER_CONTROL_ID;   
     }
     
     public static function getSortOrderColumnName()
    {
        return self::SORTORDER_COLUMN_NAME;
    }
    
    public static function getSortOrderControlCaption()
    {
        return self::SORTORDER_FIELD_NAME;    
    }
    /**
     * Set type
     *
     * @param string $type
     * @return Foiv
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    public static function getTypeControlID()
    {
        return self::TYPE_CONTROL_ID;
    }
    
    public static function getTypeColumnName()
    {
        return self::TYPE_COLUMN_NAME;
    }
    
    public static function getTypeControlCaption()
    {
        return self::TYPE_FIELD_NAME;    
    }
    /**
     * Set basictasks
     *
     * @param string $basictasks
     * @return Foiv
     */
    public function setBasictasks($basictasks)
    {
        $this->basictasks = $basictasks;

        return $this;
    }

    /**
     * Get basictasks
     *
     * @return string 
     */
    public function getBasictasks()
    {
        return $this->basictasks;
    }

    public static function getBasicTasksControlID()
    {
        return self::BASICTASKS_CONTROL_ID;
    }
    
    public static function getBasicTasksColumnName()
    {
        return self::BASICTASKS_COLUMN_NAME;
    }
    
    public static function getBasicTasksControlCaption()
    {
        return self::BASICTASKS_FIELD_NAME;    
    }
    
    /**
     * Set subsystems
     *
     * @param string $subsystems
     * @return Foiv
     */
    public function setSubsystems($subsystems)
    {
        $this->subsystems = $subsystems;

        return $this;
    }

    /**
     * Get subsystems
     *
     * @return string 
     */
    public function getSubsystems()
    {
        return $this->subsystems;
    }

    public static function getSubsystemsControlID()
    {
        return self::SUBSYSTEMS_CONTROL_ID;
    }
    
     public static function getSubsystemsColumnName()
    {
        return self::SUBSYSTEMS_COLUMN_NAME;
    }
    
    public static function getSubsystemsControlCaption()
    {
        return self::SUBSYSTEMS_FIELD_NAME;    
    }
    /**
     * Set conventions
     *
     * @param string $conventions
     * @return Foiv
     */
    public function setConventions($conventions)
    {
        $this->conventions = $conventions;

        return $this;
    }
   
    /**
     * Get conventions
     *
     * @return string 
     */
    public function getConventions()
    {
        return $this->conventions;
    }

    public static function getConventionsControlID()
    {
        return self::CONVENTIONS_CONTROL_ID;    
    }
    
     public static function getConventionsColumnName()
    {
        return self::CONVENTIONS_COLUMN_NAME;
    }
    
    public static function getConventionsControlCaption()
    {
        return self::CONVENTIONS_FIELD_NAME;    
    }
    /**
     * Set mapurl
     *
     * @param string $mapurl
     * @return Foiv
     */
    public function setMapurl($mapurl)
    {
        $this->mapurl = $mapurl;

        return $this;
    }

    /**
     * Get mapurl
     *
     * @return string 
     */
    public function getMapurl()
    {
        return $this->mapurl;
    }

    public static function getMapURLControlID()
    {
            return self::MAPURL_CONTROL_ID;
    }
    
    public static function getMapURLColumnName()
    {
        return self::MAPURL_COLUMN_NAME;
    }
    
    public static function getMapURLControlCaption()
    {
        return self::MAPURL_FIELD_NAME;    
    }
    /**
     * Set localSite
     *
     * @param string $localSite
     * @return Foiv
     */
    public function setLocalSite($localSite)
    {
        $this->localSite = $localSite;

        return $this;
    }

    /**
     * Get localSite
     *
     * @return string 
     */
    public function getLocalSite()
    {
        return $this->localSite;
    }

    public static function getLocalSiteControlID()
    {
        return self::LOCALSITE_CONTROL_ID;
    }
    
    public static function getLocalSiteColumnName()
    {
        return self::LOCALSITE_COLUMN_NAME;
    }
    
    public static function getLocalSiteControlCaption()
    {
        return self::LOCALSITE_FIELD_NAME;    
    }
    /**
     * Set engaged
     *
     * @param integer $engaged
     * @return Foiv
     */
    public function setEngaged($engaged)
    {
        //NULL значения отвергаются doctrine'ой
        //поэтому устанавливаю в 0,
        //как описано значение по умолчанию  в таблице dict_foiv  для столбца engaged
        if($engaged == null)
        {
            $this->engaged = 0;
        }
        else
        {
            $this->engaged = $engaged;
        }
        
        return $this;
    }

    /**
     * Get engaged
     *
     * @return integer 
     */
    public function getEngaged()
    {
        return $this->engaged;
    }

    public static function getEngagedControlID()
    {
        return self::ENGAGED_CONTROL_ID;
    }
    
    public static function getEngagedColumnName()
    {
        return self::ENGAGED_COLUMN_NAME;
    }
    
    public static function getEngagedControlCaption()
    {
        return self::ENGAGED_FIELD_NAME;    
    }
    /**
     * Set descriptionText
     *
     * @param string $descriptionText
     * @return Foiv
     */
    public function setDescriptionText($descriptionText)
    {
        $this->descriptionText = $descriptionText;

        return $this;
    }

    /**
     * Get descriptionText
     *
     * @return string 
     */
    public function getDescriptionText()
    {
        return $this->descriptionText;
    }

    public static function getDescriptionTextControlID()
    {
        return self::DESCRIPTION_CONTROL_ID;
    }
    
    public static function getDescriptionTextColumnName()
    {
        return self::DESCRIPTION_COLUMN_NAME;
    }
    
    public static function getDescriptionTextControlCaption()
    {
        return self::DESCRIPTION_FIELD_NAME;    
    }
    /**
     * Set stateLink
     *
     * @param string $stateLink
     * @return Foiv
     */
    public function setStateLink($stateLink)
    {
        $this->stateLink = $stateLink;

        return $this;
    }

    /**
     * Get stateLink
     *
     * @return string 
     */
    public function getStateLink()
    {
        return $this->stateLink;
    }

    public static function getStateLinkControlID()
    {
        return self::STATELINK_CONTROL_ID;
    }
    
     public static function getStateLinkColumnName()
    {
        return self::STATELINK_COLUMN_NAME;
    }
    
    public static function getStateLinkControlCaption()
    {
        return self::STATELINK_FIELD_NAME;    
    }
    /**
     * Add foiv_persons
     *
     * 
     * @return Foiv
     */
    public function addFoivPerson( $foivPersons)
    {
        $this->foiv_persons[] = $foivPersons;

        return $this;
    }

    /**
     * Remove foiv_persons
     *
     * @param \NCUO\FoivBundle\Entity\FoivPersons $foivPersons
     */
    public function removeFoivPerson(\NCUO\FoivBundle\Entity\FoivPersons $foivPersons)
    {
        $this->foiv_persons->removeElement($foivPersons);
    }

    /**
     * Get foiv_persons
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFoivPersons()
    {
        return $this->foiv_persons;
    }

    
     /**
     * Set director
     *
     * @param $director
     * 
     * @return FoivPvo
     */
    public function setDirector($director)
    {
        if($director != null)
        {
            $this->director = $director;
        }
        else
        {
            $this->director = null;
        }
        
        return $this;
    }

    
    
    /**
    * Get director
    *
    * @return string
    */
    public function getDirector()
    {
        if($this->director != null)
        {
            return $this->director;
        }
        return null;
    }
    
    

    public static function getDirectorControlID()
    {
        return self::DIRECTOR_CONTROL_ID;    
    }
    
    public static function getDirectorColumnName()
    {
        return self::DIRECTOR_COLUMN_NAME;
    }
    
    public static function getDirectorControlCaption()
    {
        return self::DIRECTOR_FIELD_NAME;    
    }
        
    
    
    /**
     * Get foiv_departments
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFoivDepartments()
    {
        return $this->foiv_departments;
    }

    /**
     * Set address
     *
     * @param \NCUO\FoivBundle\Entity\Address $address
     * @return Foiv
     */
    public function setAddress(\NCUO\FoivBundle\Entity\Address $address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return \NCUO\FoivBundle\Entity\Address 
     */
    public function getAddress()
    {
        return $this->address;
    }
	

    
    
	public function getAddressAsString()
    {
		if($this->getAddress() != null)
        {
			return $this->getAddress()->getAsString();
		}
		
		return null;
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
        return self::ADDRESS_FIELD_NAME;    
    }
    /**
     * Set reglament
     *
     * @param \NCUO\FoivBundle\Entity\FoivReglament $reglament
     * @return Foiv
     */
    public function setReglament(\NCUO\FoivBundle\Entity\FoivReglament $reglament = null)
    {
        $this->reglament = $reglament;

        return $this;
    }

    /**
     * Get reglament
     *
     * @return \NCUO\FoivBundle\Entity\FoivReglament 
     */
    public function getReglament()
    {
        return $this->reglament;
    }

    public static function getReglamentControlID()
    {
        return self::REGLAMENT_CONTROL_ID;
    }
    
     public static function getReglamentColumnName()
    {
        return self::REGLAMENT_COLUMN_NAME;
    }
    
    public static function getReglamentControlCaption()
    {
        return self::REGLAMENT_FIELD_NAME;    
    }
    /**
     * Set superfoiv
     *
     * @param \NCUO\FoivBundle\Entity\Foiv $superfoiv
     * @return Foiv
     */
    public function setSuperfoiv(\NCUO\FoivBundle\Entity\Foiv $superfoiv = null)
    {
        if($superfoiv != null)
        {
            $this->superfoiv = $superfoiv;
        }
        else
        {
            $this->superfoiv = null;
        }

        return $this;
    }

    /**
     * Get superfoiv
     *
     * @return \NCUO\FoivBundle\Entity\Foiv 
     */
    public function getSuperfoiv()
    {
        return $this->superfoiv;
    }

    public static function getSuperFoivControlID()
    {
        return self::SUPERFOIV_CONTROL_ID;
    }
    
    public static function getSuperFoivColumnName()
    {
        return self::SUPERFOIV_COLUMN_NAME;
    }
    
    public static function getSuperFoivControlCaption()
    {
        return self::SUPERFOIV_FIELD_NAME;    
    }
    /**
     * Set foiv_children
     *
     * @param \NCUO\FoivBundle\Entity\Foiv $foiv_children
     * @return Foiv
     */
    public function setFoivchildren(\NCUO\FoivBundle\Entity\Foiv $foiv_children = null)
    {
        $this->foiv_children = $foiv_children;

        return $this;
    }

    /**
     * Get foiv_children
     *
     * @return \NCUO\FoivBundle\Entity\Foiv 
     */
    public function getFoivchildren()
    {
        return $this->foiv_children;
    }

    /**
     * Add sources
     *
     * @param App\NCUO\EifBundle\Entity\Source $sources
     * @return Foiv
     */
    public function addSource(App\NCUO\EifBundle\Entity\Source $sources)
    {
        $this->sources[] = $sources;

        return $this;
    }

    /**
     * Remove sources
     *
     * @param App\NCUO\EifBundle\Entity\Source $sources
     */
    public function removeSource(App\NCUO\EifBundle\Entity\Source $sources)
    {
        $this->sources->removeElement($sources);
    }

    /**
     * Get sources
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSources()
    {
        return $this->sources;
    }
    
    /*
     * Add owner
     *
     * @param App\NCUO\NsiBundle\Entity\Owner $owner
     * @return Foiv
    public function addOwner(App\NCUO\NsiBundle\Entity\Owner $owner)
    {
        $this->owners[] = $owner;

        return $this;
    }
     */

    /*
     * Remove owner
     *
     * @param App\NCUO\NsiBundle\Entity\Owner $owner
    public function removeOwner(App\NCUO\NsiBundle\Entity\Owner $owner)
    {
        $this->owners->removeElement($owner);
    }
     */

    /*
     * Get owners
     *
     * @return \Doctrine\Common\Collections\Collection 
    public function getOwners()
    {
        return $this->owners;
    }    
     */
    
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
    
    public static function IsMandatory($FieldName)
    {
        
        if($FieldName == null )
        {
            throw new \Exception('Не задано имя столбца таблицы dict_foiv');
        }
        
        switch($FieldName)
        {
            case self::FULL_NAME_COLUMN_NAME: return true;
            case self::SHORT_NAME_COLUMN_NAME: return false;
            case self::SUPERFOIV_COLUMN_NAME: return true;
            case self::SITENAME_COLUMN_NAME: return false;
            case self::SITEURL_COLUMN_NAME: return false;
            case self::TYPE_COLUMN_NAME: return false;
            case self::DIRECTOR_COLUMN_NAME: return true;
            case self::STATELINK_COLUMN_NAME: return false;
            case self::DESCRIPTION_COLUMN_NAME: return false;
            case self::ADDRESS_COLUMN_NAME: return true;
            case self::VERSION_COLUMN_NAME: return true;
            case self::ICONSTYLE_COLUMN_NAME: return false;
            case self::SORTORDER_COLUMN_NAME: return true;
            case self::BASICTASKS_COLUMN_NAME: return false;
            case self::SUBSYSTEMS_COLUMN_NAME: return false;
            case self::CONVENTIONS_COLUMN_NAME: return false;
            case self::MAPURL_COLUMN_NAME: return false;
            case self::LOCALSITE_COLUMN_NAME: return false;
            case self::ENGAGED_COLUMN_NAME: return true;
            case self::REGLAMENT_COLUMN_NAME: return true;

            default:  throw new \Exception('Не найдено имя столбца "'.$FieldName.'" таблицы dict_foiv');
        }
        
        
        
    }
 
    /*
     *Функция получения списка полей
     *Данная функция генерирует ассоциативный массив по каждому полю, то есть для каждого HTML-контрола
     * Порядок данных в массив таков:
     * ключ массива: ID HTML-контрола 
     * Поле №1: тип контрола (editbox, combobox, textarea или updown )
     * Поле №2 : заголовок к данному контролу
     * Поле №3: признак того, что  данный контрол обязателен для заполнения или выбора данных
     * Примечание:
        для некотрых полей № 3 , имеющее реализацию в виде контрола типа updown, принудительно проставлено значение
        флага обязательного использования данного поля при вводе данных. Этот шаг продиктован тем, что Doctrine посылает в БД
        незаданные целочисленные значение полей  в виде пустой строки, а не NULL  и из-за этого БД ругается.
     *
     * $OrmMetaData - объект метаинформации об конкретном классе, отображаемого из Doctrine. В нашем случает это Foiv.
     *                                 Используется для выявления обязательных или необязательных полей
     */
     public static function getFieldNames($OrmMetaData)
    {
        
        return Array( self::FULL_NAME_CONTROL_ID         => array( self::FULL_NAME_CONTROL_TYPE,     self::FULL_NAME_FIELD_NAME),
                                self::SHORT_NAME_CONTROL_ID     => array( self::SHORT_NAME_CONTROL_TYPE,  self::SHORT_NAME_FIELD_NAME),
                                self::SUPERFOIV_CONTROL_ID           => array( self::SUPERFOIV_CONTROL_TYPE,       self::SUPERFOIV_FIELD_NAME),
                                self::SITENAME_CONTROL_ID             => array( self::SITENAME_CONTROL_TYPE, self::SITENAME_FIELD_NAME),
                                self::SITEURL_CONTROL_ID                 => array( self::SITEURL_CONTROL_TYPE,      self::SITEURL_FIELD_NAME),
                                self::TYPE_CONTROL_ID                      => array( self::TYPE_CONTROL_TYPE,      self::TYPE_FIELD_NAME),
                                self::DIRECTOR_CONTROL_ID              => array( self::DIRECTOR_CONTROL_TYPE,      self::DIRECTOR_FIELD_NAME),
                                self::STATELINK_CONTROL_ID             => array( self::STATELINK_CONTROL_TYPE,      self::STATELINK_FIELD_NAME),
                                self::DESCRIPTION_CONTROL_ID        => array( self::DESCRIPTION_CONTROL_TYPE,      self::DESCRIPTION_FIELD_NAME),
                                self::ADDRESS_CONTROL_ID               => array( self::ADDRESS_CONTROL_TYPE,      self::ADDRESS_FIELD_NAME),
                                self::VERSION_CONTROL_ID               => array( self::VERSION_CONTROL_TYPE,      self::VERSION_FIELD_NAME),  //true
                                self::ICONSTYLE_CONTROL_ID            => array( self::ICONSTYLE_CONTROL_TYPE,      self::ICONSTYLE_FIELD_NAME),
                                self::SORTORDER_CONTROL_ID          => array( self::SORTORDER_CONTROL_TYPE,      self::SORTORDER_FIELD_NAME), //true
                                self::BASICTASKS_CONTROL_ID          => array( self::BASICTASKS_CONTROL_TYPE,      self::BASICTASKS_FIELD_NAME),
                                self::SUBSYSTEMS_CONTROL_ID        => array( self::SUBSYSTEMS_CONTROL_TYPE,      self::SUBSYSTEMS_FIELD_NAME),
                                self::CONVENTIONS_CONTROL_ID     => array( self::CONVENTIONS_CONTROL_TYPE,      self::CONVENTIONS_FIELD_NAME),
                                self::MAPURL_CONTROL_ID               => array( self::MAPURL_CONTROL_TYPE,      self::MAPURL_FIELD_NAME),
                                self::LOCALSITE_CONTROL_ID             => array( self::LOCALSITE_CONTROL_TYPE,      self::LOCALSITE_FIELD_NAME),
                                self::ENGAGED_CONTROL_ID              => array( self::ENGAGED_CONTROL_TYPE,      self::ENGAGED_FIELD_NAME),//true
                                self::REGLAMENT_CONTROL_ID         => array( self::REGLAMENT_CONTROL_TYPE,      self::REGLAMENT_FIELD_NAME)
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
 
     
     * Foiv - ФОИВ-объект , содержащий искомые данные
     * OrmMetaData - объект метаинформации об конкретном классе, отображаемого из Doctrine. В нашем случает это Foiv.
     *                              Используется для выявления обязательных или необязательных полей
     */
    public static function getFieldNamesWithData($Foiv, $OrmMetaData)
    {
        return Array( self::FULL_NAME_CONTROL_ID         => array( self::FULL_NAME_CONTROL_TYPE,     self::FULL_NAME_FIELD_NAME, $Foiv->getName()), 
                                self::SHORT_NAME_CONTROL_ID     => array( self::SHORT_NAME_CONTROL_TYPE,     self::SHORT_NAME_FIELD_NAME, $Foiv->getShortname()),
                                self::SUPERFOIV_CONTROL_ID           => array( self::SUPERFOIV_CONTROL_TYPE,    self::SUPERFOIV_FIELD_NAME, $Foiv->getSuperfoiv()),
                                self::SITENAME_CONTROL_ID             => array( self::SITENAME_CONTROL_TYPE, self::SITENAME_FIELD_NAME, $Foiv->getSitename()),
                                self::SITEURL_CONTROL_ID                 => array( self::SITEURL_CONTROL_TYPE,      self::SITEURL_FIELD_NAME, $Foiv->getSiteurl()),
                                self::TYPE_CONTROL_ID                      => array( self::TYPE_CONTROL_TYPE,      self::TYPE_FIELD_NAME, $Foiv->getType()),
                                self::DIRECTOR_CONTROL_ID              => array( self::DIRECTOR_CONTROL_TYPE,      self::DIRECTOR_FIELD_NAME, $Foiv->getDirector()), 
                                self::STATELINK_CONTROL_ID             => array( self::STATELINK_CONTROL_TYPE,      self::STATELINK_FIELD_NAME, $Foiv->getStateLink()),
                                self::DESCRIPTION_CONTROL_ID        => array( self::DESCRIPTION_CONTROL_TYPE,      self::DESCRIPTION_FIELD_NAME, $Foiv->getDescriptionText()),
                                self::ADDRESS_CONTROL_ID               => array( self::ADDRESS_CONTROL_TYPE,      self::ADDRESS_FIELD_NAME, /*$Foiv->getAddress()->getAsString()*/ $Foiv->getAddressAsString()), 
                                self::VERSION_CONTROL_ID               => array( self::VERSION_CONTROL_TYPE,      self::VERSION_FIELD_NAME, $Foiv->getVersion()),// true
                                self::ICONSTYLE_CONTROL_ID            => array( self::ICONSTYLE_CONTROL_TYPE,      self::ICONSTYLE_FIELD_NAME, $Foiv->getIconstyle()),
                                self::SORTORDER_CONTROL_ID          => array( self::SORTORDER_CONTROL_TYPE,      self::SORTORDER_FIELD_NAME, $Foiv->getSortOrder()),// true
                                self::BASICTASKS_CONTROL_ID          => array( self::BASICTASKS_CONTROL_TYPE,      self::BASICTASKS_FIELD_NAME, $Foiv->getBasictasks()),
                                self::SUBSYSTEMS_CONTROL_ID        => array( self::SUBSYSTEMS_CONTROL_TYPE,      self::SUBSYSTEMS_FIELD_NAME, $Foiv->getSubsystems()),
                                self::CONVENTIONS_CONTROL_ID     => array( self::CONVENTIONS_CONTROL_TYPE,      self::CONVENTIONS_FIELD_NAME, $Foiv->getConventions()),
                                self::MAPURL_CONTROL_ID               => array( self::MAPURL_CONTROL_TYPE,      self::MAPURL_FIELD_NAME, $Foiv->getMapurl()),
                                self::LOCALSITE_CONTROL_ID             => array( self::LOCALSITE_CONTROL_TYPE,      self::LOCALSITE_FIELD_NAME, $Foiv->getLocalSite()),
                                self::ENGAGED_CONTROL_ID              => array( self::ENGAGED_CONTROL_TYPE,      self::ENGAGED_FIELD_NAME, $Foiv->getEngaged()),//true
                                self::REGLAMENT_CONTROL_ID         => array( self::REGLAMENT_CONTROL_TYPE,      self::REGLAMENT_FIELD_NAME, $Foiv->getReglament())
                             );
    }
}
