<?php

namespace App\NCUO\FoivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Roiv
 *
 * @ORM\Table(name="eif_data2.dict_roiv", indexes={@ORM\Index(name="IDX_B64F7F7C1E90D3F0", columns={"director"}), @ORM\Index(name="IDX_B64F7F7C733069CA", columns={"superroiv"}), @ORM\Index(name="IDX_B64F7F7C71DBFF39", columns={"foiv"}), @ORM\Index(name="IDX_B64F7F7C5426FEB3", columns={"sitcenter_fk"}), @ORM\Index(name="IDX_B64F7F7CF62F176", columns={"region"})})
 * @ORM\Entity(repositoryClass="RoivRepository")
 */
class Roiv
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="eif_data2.dict_roiv_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="functions", type="text", nullable=true)
     */
    private $functions;

    /**
     * @var string
     *
     * @ORM\Column(name="site_url", type="string", length=255, nullable=true)
     */
    private $siteUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="map_url", type="string", length=255, nullable=true)
     */
    private $mapUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="comments", type="text", nullable=true)
     */
    private $comments;

    /**
     * @var NCUO\FoivBundle\Entity\RoivPersons
     *
     * @ORM\ManyToOne(targetEntity="NCUO\FoivBundle\Entity\RoivPersons")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="director", referencedColumnName="id")
     * })
     */
    private $director;

    /**
     * @var NCUO\FoivBundle\Entity\Roiv
     *
     * @ORM\ManyToOne(targetEntity="NCUO\FoivBundle\Entity\Roiv")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="superroiv", referencedColumnName="id")
     * })
     */
    private $superroiv;

    /**
     * @var NCUO\FoivBundle\Entity\Foiv
     *
     * @ORM\ManyToOne(targetEntity="NCUO\FoivBundle\Entity\Foiv")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="foiv", referencedColumnName="id")
     * })
     */
    private $foiv;

    /**
     * @var NCUO\FoivBundle\Entity\RoivSitcenter
     *
     * @ORM\ManyToOne(targetEntity="NCUO\FoivBundle\Entity\RoivSitcenter")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sitcenter_fk", referencedColumnName="id")
     * })
     */
    private $sitcenterFk;

    /**
     * @var NCUO\FoivBundle\Entity\Regions
     *
     * @ORM\ManyToOne(targetEntity="NCUO\FoivBundle\Entity\Regions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="region", referencedColumnName="id")
     * })
     */
    private $region;

    /**
     * @ORM\OneToMany(targetEntity="\NCUO\EifBundle\Entity\Source", mappedBy="roiv", fetch="EXTRA_LAZY")
     */
    
    private $sources;
    
    /**
     * @ORM\OneToMany(targetEntity="\NCUO\NsiBundle\Entity\Owner", mappedBy="roiv", fetch="EXTRA_LAZY")
     */
    
    private $owners;      
    

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
     * @return Roiv
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
     * Set functions
     *
     * @param string $functions
     * @return Roiv
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

    /**
     * Set siteUrl
     *
     * @param string $siteUrl
     * @return Roiv
     */
    public function setSiteUrl($siteUrl)
    {
        $this->siteUrl = $siteUrl;

        return $this;
    }

    /**
     * Get siteUrl
     *
     * @return string 
     */
    public function getSiteUrl()
    {
        return $this->siteUrl;
    }

    /**
     * Set mapUrl
     *
     * @param string $mapUrl
     * @return Roiv
     */
    public function setMapUrl($mapUrl)
    {
        $this->mapUrl = $mapUrl;

        return $this;
    }

    /**
     * Get mapUrl
     *
     * @return string 
     */
    public function getMapUrl()
    {
        return $this->mapUrl;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return Roiv
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

    /**
     * Set phone
     *
     * @param string $phone
     * @return Roiv
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

    /**
     * Set comments
     *
     * @param string $comments
     * @return Roiv
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get comments
     *
     * @return string 
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set director
     *
     * @param \NCUO\FoivBundle\Entity\RoivPersons $director
     * @return Roiv
     */
    public function setDirector(\NCUO\FoivBundle\Entity\RoivPersons $director = null)
    {
        $this->director = $director;

        return $this;
    }

    /**
     * Get director
     *
     * @return \NCUO\FoivBundle\Entity\RoivPersons 
     */
    public function getDirector()
    {
        return $this->director;
    }

    /**
     * Set superroiv
     *
     * @param \NCUO\FoivBundle\Entity\Roiv $superroiv
     * @return Roiv
     */
    public function setSuperroiv(\NCUO\FoivBundle\Entity\Roiv $superroiv = null)
    {
        $this->superroiv = $superroiv;

        return $this;
    }

    /**
     * Get superroiv
     *
     * @return \NCUO\FoivBundle\Entity\Roiv 
     */
    public function getSuperroiv()
    {
        return $this->superroiv;
    }

    /**
     * Set foiv
     *
     * @param \NCUO\FoivBundle\Entity\Foiv $foiv
     * @return Roiv
     */
    public function setFoiv(\NCUO\FoivBundle\Entity\Foiv $foiv = null)
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

    /**
     * Set sitcenterFk
     *
     * @param \NCUO\FoivBundle\Entity\RoivSitcenter $sitcenterFk
     * @return Roiv
     */
    public function setSitcenterFk(\NCUO\FoivBundle\Entity\RoivSitcenter $sitcenterFk = null)
    {
        $this->sitcenterFk = $sitcenterFk;

        return $this;
    }

    /**
     * Get sitcenterFk
     *
     * @return \NCUO\FoivBundle\Entity\RoivSitcenter 
     */
    public function getSitcenterFk()
    {
        return $this->sitcenterFk;
    }

    /**
     * Set region
     *
     * @param \NCUO\FoivBundle\Entity\Regions $region
     * @return Roiv
     */
    public function setRegion(\NCUO\FoivBundle\Entity\Regions $region = null)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region
     *
     * @return \NCUO\FoivBundle\Entity\Regions 
     */
    public function getRegion()
    {
        return $this->region;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sources = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add sources
     *
     * @param \NCUO\EifBundle\Entity\Source $sources
     * @return Roiv
     */
    public function addSource(\NCUO\EifBundle\Entity\Source $sources)
    {
        $this->sources[] = $sources;

        return $this;
    }

    /**
     * Remove sources
     *
     * @param \NCUO\EifBundle\Entity\Source $sources
     */
    public function removeSource(\NCUO\EifBundle\Entity\Source $sources)
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
    
    /**
     * Add owner
     *
     * @param \NCUO\NsiBundle\Entity\Owner $owner
     * @return Foiv
     */
    public function addOwner(\NCUO\NsiBundle\Entity\Owner $owner)
    {
        $this->owners[] = $owner;

        return $this;
    }

    /**
     * Remove owner
     *
     * @param \NCUO\NsiBundle\Entity\Owner $owner
     */
    public function removeOwner(\NCUO\NsiBundle\Entity\Owner $owner)
    {
        $this->owners->removeElement($owner);
    }

    /**
     * Get owners
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOwners()
    {
        return $this->owners;
    }     
    
    public function __toString()
    {
    	return $this->getName();
    }
    
}
