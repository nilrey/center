<?php

namespace App\NCUO\FoivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FederalDistricts
 *
 * @ORM\Table(name="eif_data2.dict_federal_districts", indexes={@ORM\Index(name="IDX_3CEFD8EA4DE79FE0", columns={"svg_path"})})
 * @ORM\Entity
 */
class FederalDistricts
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="eif_data2.dict_federal_districts_id_seq", allocationSize=1, initialValue=1)
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
     * @var integer
     *
     * @ORM\Column(name="subjects", type="integer", nullable=true)
     */
    private $subjects;

    /**
     * @var string
     *
     * @ORM\Column(name="center", type="string", length=255, nullable=true)
     */
    private $center;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="map_url", type="string", length=1024, nullable=true)
     */
    private $mapUrl;

    /**
     * @var NCUO\FoivBundle\Entity\SvgPaths
     *
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\SvgPaths")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="svg_path", referencedColumnName="id")
     * })
     */
    private $svgPath;



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
     * @return FederalDistricts
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
     * Set territory
     *
     * @param integer $territory
     * @return FederalDistricts
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
     * @return FederalDistricts
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
     * Set subjects
     *
     * @param integer $subjects
     * @return FederalDistricts
     */
    public function setSubjects($subjects)
    {
        $this->subjects = $subjects;

        return $this;
    }

    /**
     * Get subjects
     *
     * @return integer 
     */
    public function getSubjects()
    {
        return $this->subjects;
    }

    /**
     * Set center
     *
     * @param string $center
     * @return FederalDistricts
     */
    public function setCenter($center)
    {
        $this->center = $center;

        return $this;
    }

    /**
     * Get center
     *
     * @return string 
     */
    public function getCenter()
    {
        return $this->center;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return FederalDistricts
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
     * @return FederalDistricts
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
     * Set svgPath
     *
     * @return FederalDistricts
     */
    public function setSvgPath( $svgPath = null)
    {
        $this->svgPath = $svgPath;

        return $this;
    }

    /**
     * Get svgPath
     *
     * @return App\NCUO\FoivBundle\Entity\SvgPaths 
     */
    public function getSvgPath()
    {
        return $this->svgPath;
    }
}
