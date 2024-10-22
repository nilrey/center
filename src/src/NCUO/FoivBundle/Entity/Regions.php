<?php

namespace App\NCUO\FoivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Regions
 *
 * @ORM\Table(name="eif_data2.dict_regions", indexes={@ORM\Index(name="IDX_8B4641D4993BBEA7", columns={"military_district_id"}), @ORM\Index(name="IDX_8B4641D44DE79FE0", columns={"svg_path"}), @ORM\Index(name="IDX_8B4641D4B08FA272", columns={"district_id"})})
 * @ORM\Entity
 */
class Regions
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="eif_data2.dict_regions_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="okato_code", type="integer", nullable=true)
     */
    private $okatoCode;

    /**
     * @var integer
     *
     * @ORM\Column(name="territory", type="integer", nullable=true)
     */
    private $territory;

    /**
     * @var integer
     *
     * @ORM\Column(name="population", type="integer", nullable=true)
     */
    private $population;

    /**
     * @var string
     *
     * @ORM\Column(name="capital", type="string", length=255, nullable=true)
     */
    private $capital;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="map_url", type="string", length=1024, nullable=true)
     */
    private $mapUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="web_site", type="text", nullable=true)
     */
    private $webSite;

    /**
     * @var App\NCUO\FoivBundle\Entity\MilitaryDistricts
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\MilitaryDistricts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="military_district_id", referencedColumnName="id")
     * })
     */
    private $militaryDistrict;

    /**
     * @var App\NCUO\FoivBundle\Entity\SvgPaths
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\SvgPaths")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="svg_path", referencedColumnName="id")
     * })
     */
    private $svgPath;

    /**
     * @var App\NCUO\FoivBundle\Entity\FederalDistricts
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\FederalDistricts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="district_id", referencedColumnName="id")
     * })
     */
    private $district;



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
     * @return Regions
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
     * Set okatoCode
     *
     * @param integer $okatoCode
     * @return Regions
     */
    public function setOkatoCode($okatoCode)
    {
        $this->okatoCode = $okatoCode;

        return $this;
    }

    /**
     * Get okatoCode
     *
     * @return integer 
     */
    public function getOkatoCode()
    {
        return $this->okatoCode;
    }

    /**
     * Set territory
     *
     * @param integer $territory
     * @return Regions
     */
    public function setTerritory($territory)
    {
        $this->territory = $territory;

        return $this;
    }

    /**
     * Get territory
     *
     * @return integer 
     */
    public function getTerritory()
    {
        return $this->territory;
    }

    /**
     * Set population
     *
     * @param integer $population
     * @return Regions
     */
    public function setPopulation($population)
    {
        $this->population = $population;

        return $this;
    }

    /**
     * Get population
     *
     * @return integer 
     */
    public function getPopulation()
    {
        return $this->population;
    }

    /**
     * Set capital
     *
     * @param string $capital
     * @return Regions
     */
    public function setCapital($capital)
    {
        $this->capital = $capital;

        return $this;
    }

    /**
     * Get capital
     *
     * @return string 
     */
    public function getCapital()
    {
        return $this->capital;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Regions
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
     * Set mapUrl
     *
     * @param string $mapUrl
     * @return Regions
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
     * Set webSite
     *
     * @param string $webSite
     * @return Regions
     */
    public function setWebSite($webSite)
    {
        $this->webSite = $webSite;

        return $this;
    }

    /**
     * Get webSite
     *
     * @return string 
     */
    public function getWebSite()
    {
        return $this->webSite;
    }

    /**
     * Set militaryDistrict
     *
     * @param App\NCUO\FoivBundle\Entity\MilitaryDistricts $militaryDistrict
     * @return Regions
     */
    public function setMilitaryDistrict(App\NCUO\FoivBundle\Entity\MilitaryDistricts $militaryDistrict = null)
    {
        $this->militaryDistrict = $militaryDistrict;

        return $this;
    }

    /**
     * Get militaryDistrict
     *
     * @return \NCUO\FoivBundle\Entity\MilitaryDistricts 
     */
    public function getMilitaryDistrict()
    {
        return $this->militaryDistrict;
    }

    /**
     * Set svgPath
     *
     * @param App\NCUO\FoivBundle\Entity\SvgPaths $svgPath
     * @return Regions
     */
    public function setSvgPath(App\NCUO\FoivBundle\Entity\SvgPaths $svgPath = null)
    {
        $this->svgPath = $svgPath;

        return $this;
    }

    /**
     * Get svgPath
     *
     * @return \NCUO\FoivBundle\Entity\SvgPaths 
     */
    public function getSvgPath()
    {
        return $this->svgPath;
    }

    /**
     * Set district
     *
     * @param App\NCUO\FoivBundle\Entity\FederalDistricts $district
     * @return Regions
     */
    public function setDistrict(App\NCUO\FoivBundle\Entity\FederalDistricts $district = null)
    {
        $this->district = $district;

        return $this;
    }

    /**
     * Get district
     *
     * @return \NCUO\FoivBundle\Entity\FederalDistricts 
     */
    public function getDistrict()
    {
        return $this->district;
    }
}
