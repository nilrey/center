<?php

namespace App\NCUO\FoivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Systems
 *
 * @ORM\Table(name="eif_data.dict_systems", indexes={@ORM\Index(name="IDX_488CE09571DBFF39", columns={"foiv"})})
 * @ORM\Entity(repositoryClass="SystemsRepository")
 */
class Systems
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="eif_data.seq_system", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="eng_name", type="string", length=255, nullable=true)
     */
    private $engName;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="icon", type="blob", nullable=true)
     */
    private $icon;

    /**
     * @var string
     *
     * @ORM\Column(name="iconstyle", type="string", length=255, nullable=true)
     */
    private $iconstyle;

    /**
     * @var string
     *
     * @ORM\Column(name="owner", type="string", length=255, nullable=true)
     */
    private $owner;

    /**
     * @var string
     *
     * @ORM\Column(name="developer", type="string", length=255, nullable=true)
     */
    private $developer;

    /**
     * @var string
     *
     * @ORM\Column(name="contactperson", type="string", length=255, nullable=true)
     */
    private $contactperson;

    /**
     * @var string
     *
     * @ORM\Column(name="contactphone", type="string", length=255, nullable=true)
     */
    private $contactphone;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="login", type="string", length=255, nullable=true)
     */
    private $login;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=true)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @var integer
     *
     * @ORM\Column(name="version", type="integer", nullable=true)
     */
    private $version;

    /**
     * @var string
     *
     * @ORM\Column(name="autologin_form", type="string", nullable=true)
     */
    private $autologinForm;

    /**
     * @var string
     *
     * @ORM\Column(name="registry_link", type="string", nullable=true)
     */
    private $registryLink;

    /**
     * @var App\NCUO\FoivBundle\Entity\Foiv
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\Foiv")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="foiv", referencedColumnName="id")
     * })
     */
    private $foiv;

    /**
     * @var App\NCUO\EifBundle\Entity\Protocol
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\EifBundle\Entity\Protocol")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="protocol_id", referencedColumnName="id_protocol" )
     * })
     */
    private $protocol_id;

    /**
     * @var App\NCUO\FoivBundle\Entity\StatusYn
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\StatusYn")
     * @ORM\JoinColumn(name="is_integration", referencedColumnName="id")
     */
    private $isIntegration;
    
    /**
     * @var App\NCUO\FoivBundle\Entity\Status3Opt
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\Status3Opt")
     * @ORM\JoinColumn(name="protocol_its", referencedColumnName="id")
     */
    private $protocolIts;
    
    /**
     * @var App\NCUO\FoivBundle\Entity\StatusYn
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\StatusYn")
     * @ORM\JoinColumn(name="vipnet", referencedColumnName="id")
     */
    private $vipnet;

    /**
     * @var App\NCUO\FoivBundle\Entity\Status3Opt
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\Status3Opt")
     * @ORM\JoinColumn(name="integration_spo", referencedColumnName="id")
     */
    private $integrationSpo;
    
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
     * Set engName
     *
     * @param string $engName
     * @return Systems
     */
    public function setEngName($engName)
    {
        $this->engName = $engName;

        return $this;
    }

    /**
     * Get engName
     *
     * @return string 
     */
    public function getEngName()
    {
        return $this->engName;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Systems
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

    /**
     * Set icon
     *
     * @param string $icon
     * @return Systems
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon
     *
     * @return string 
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set iconstyle
     *
     * @param string $iconstyle
     * @return Systems
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

    /**
     * Set owner
     *
     * @param string $owner
     * @return Systems
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return string 
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set developer
     *
     * @param string $developer
     * @return Systems
     */
    public function setDeveloper($developer)
    {
        $this->developer = $developer;

        return $this;
    }

    /**
     * Get developer
     *
     * @return string 
     */
    public function getDeveloper()
    {
        return $this->developer;
    }

    /**
     * Set contactperson
     *
     * @param string $contactperson
     * @return Systems
     */
    public function setContactperson($contactperson)
    {
        $this->contactperson = $contactperson;

        return $this;
    }

    /**
     * Get contactperson
     *
     * @return string 
     */
    public function getContactperson()
    {
        return $this->contactperson;
    }

    /**
     * Set contactphone
     *
     * @param string $contactphone
     * @return Systems
     */
    public function setContactphone($contactphone)
    {
        $this->contactphone = $contactphone;

        return $this;
    }

    /**
     * Get contactphone
     *
     * @return string 
     */
    public function getContactphone()
    {
        return $this->contactphone;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Systems
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Systems
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

    /**
     * Set login
     *
     * @param string $login
     * @return Systems
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * Get login
     *
     * @return string 
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return Systems
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Systems
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set version
     *
     * @param integer $version
     * @return Systems
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
    
    /**
     * Set autologinForm
     *
     * @param string $autologinForm
     * @return Systems
     */
    public function setAutologinForm($autologinForm)
    {
        $this->autologinForm = $autologinForm;

        return $this;
    }

    /**
     * Get autologinForm
     *
     * @return string 
     */
    public function getAutologinForm()
    {
        return $this->autologinForm;
    }

    /**
     * Set registryLink
     *
     * @param string $registryLink
     * @return Systems
     */
    public function setRegistryLink($registryLink)
    {
        $this->registryLink = $registryLink;

        return $this;
    }

    /**
     * Get registryLink
     *
     * @return string 
     */
    public function getRegistryLink()
    {
        return $this->registryLink;
    }

    /**
     * Set foiv
     *
     * @param App\NCUO\FoivBundle\Entity\Foiv $foiv
     * @return Systems
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
     * Set protocol_id
     *
     * @param integer $protocol_id
     * @return Systems
     */
    public function setProtocolId($protocol_id)
    {
        $this->protocol_id = $protocol_id;

        return $this;
    }

    /**
     * Get protocol_id
     *
     * @return integer 
     */
    public function getProtocolId()
    {
        return $this->protocol_id;
    }
    
    /**
     * Set isIntegration
     *
     * @param App\NCUO\FoivBundle\Entity\StatusYn $id
     * @return StatusYn
     */
    public function setIsIntegration(App\NCUO\FoivBundle\Entity\StatusYn $id = null)
    {
        $this->isIntegration = $id;

        return $this;
    }

    /**
     * Get isIntegration
     *
     * @return App\NCUO\FoivBundle\Entity\StatusYn 
     */
    public function getIsIntegration()
    {
        return $this->isIntegration;
    }
    
    /**
     * Set protocol_its
     *
     * @param App\NCUO\FoivBundle\Entity\Status3Opt $id
     * @return Status3Opt
     */
    public function setProtocolIts(App\NCUO\FoivBundle\Entity\Status3Opt $id = null)
    {
        $this->protocolIts = $id;

        return $this;
    }

    /**
     * Get protocol_its
     *
     * @return App\NCUO\FoivBundle\Entity\Status3Opt 
     */
    public function getProtocolIts()
    {
        return $this->protocolIts;
    }

    /**
     * Set vipnet
     *
     * @param App\NCUO\FoivBundle\Entity\StatusYn $id
     * @return StatusYn
     */
    public function setVipnet(App\NCUO\FoivBundle\Entity\StatusYn $id = null)
    {
        $this->vipnet = $id;

        return $this;
    }

    /**
     * Get vipnet
     *
     * @return App\NCUO\FoivBundle\Entity\StatusYn 
     */
    public function getVipnet()
    {
        return $this->vipnet;
    }
    
    /**
     * Set integration_spo
     *
     * @param App\NCUO\FoivBundle\Entity\Status3Opt $id
     * @return Status3Opt
     */
    public function setIntegrationSpo(App\NCUO\FoivBundle\Entity\Status3Opt $id = null)
    {
        $this->integrationSpo = $id;

        return $this;
    }

    /**
     * Get integration_spo
     *
     * @return App\NCUO\FoivBundle\Entity\Status3Opt 
     */
    public function getIntegrationSpo()
    {
        return $this->integrationSpo;
    }

}
