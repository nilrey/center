<?php

namespace App\NCUO\FoivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MilitaryDistricts
 *
 * @ORM\Table(name="eif_data2.dict_military_districts")
 * @ORM\Entity
 */
class MilitaryDistricts
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="eif_data2.dict_military_districts_id_seq", allocationSize=1, initialValue=1)
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
     * @ORM\Column(name="map_url", type="string", length=1024, nullable=true)
     */
    private $mapUrl;



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
     * @return MilitaryDistricts
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
     * Set mapUrl
     *
     * @param string $mapUrl
     * @return MilitaryDistricts
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
}
