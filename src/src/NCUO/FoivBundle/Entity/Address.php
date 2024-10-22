<?php

namespace App\NCUO\FoivBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Address
 *
 * @ORM\Table(name="eif_data2.dict_address")
 * @ORM\Entity
 */
class Address
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="eif_data2.dict_address_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="okato_code", type="string", nullable=true)
     */
    private $okatoCode;

    /**
     * @var string
     *
     * @ORM\Column(name="kladr_code", type="string", nullable=true)
     */
    private $kladrCode;

    /**
     * @var string
     *
     * @ORM\Column(name="region", type="string", nullable=true)
     */
    private $region;

    /**
     * @var string
     *
     * @ORM\Column(name="district", type="string", nullable=true)
     */
    private $district;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", nullable=true)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="village", type="string", nullable=true)
     */
    private $village;

    /**
     * @var string
     *
     * @ORM\Column(name="street", type="string", nullable=true)
     */
    private $street;

    /**
     * @var string
     *
     * @ORM\Column(name="house_number", type="string", nullable=true)
     */
    private $houseNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="house_sub_number", type="string", nullable=true)
     */
    private $houseSubNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="building_number", type="string", nullable=true)
     */
    private $buildingNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="index", type="string", nullable=true)
     */
    private $index;



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
     * Set okatoCode
     *
     * @param string $okatoCode
     * @return Address
     */
    public function setOkatoCode($okatoCode)
    {
        $this->okatoCode = $okatoCode;

        return $this;
    }

    /**
     * Get okatoCode
     *
     * @return string 
     */
    public function getOkatoCode()
    {
        return $this->okatoCode;
    }

    /**
     * Set kladrCode
     *
     * @param string $kladrCode
     * @return Address
     */
    public function setKladrCode($kladrCode)
    {
        $this->kladrCode = $kladrCode;

        return $this;
    }

    /**
     * Get kladrCode
     *
     * @return string 
     */
    public function getKladrCode()
    {
        return $this->kladrCode;
    }

    /**
     * Set region
     *
     * @param string $region
     * @return Address
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region
     *
     * @return string 
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set district
     *
     * @param string $district
     * @return Address
     */
    public function setDistrict($district)
    {
        $this->district = $district;

        return $this;
    }

    /**
     * Get district
     *
     * @return string 
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Address
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set village
     *
     * @param string $village
     * @return Address
     */
    public function setVillage($village)
    {
        $this->village = $village;

        return $this;
    }

    /**
     * Get village
     *
     * @return string 
     */
    public function getVillage()
    {
        return $this->village;
    }

    /**
     * Set street
     *
     * @param string $street
     * @return Address
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street
     *
     * @return string 
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set houseNumber
     *
     * @param string $houseNumber
     * @return Address
     */
    public function setHouseNumber($houseNumber)
    {
        $this->houseNumber = $houseNumber;

        return $this;
    }

    /**
     * Get houseNumber
     *
     * @return string 
     */
    public function getHouseNumber()
    {
        return $this->houseNumber;
    }

    /**
     * Set houseSubNumber
     *
     * @param string $houseSubNumber
     * @return Address
     */
    public function setHouseSubNumber($houseSubNumber)
    {
        $this->houseSubNumber = $houseSubNumber;

        return $this;
    }

    /**
     * Get houseSubNumber
     *
     * @return string 
     */
    public function getHouseSubNumber()
    {
        return $this->houseSubNumber;
    }

    /**
     * Set buildingNumber
     *
     * @param string $buildingNumber
     * @return Address
     */
    public function setBuildingNumber($buildingNumber)
    {
        $this->buildingNumber = $buildingNumber;

        return $this;
    }

    /**
     * Get buildingNumber
     *
     * @return string 
     */
    public function getBuildingNumber()
    {
        return $this->buildingNumber;
    }

    /**
     * Set index
     *
     * @param string $index
     * @return Address
     */
    public function setIndex($index)
    {
        $this->index = $index;

        return $this;
    }

    /**
     * Get index
     *
     * @return string 
     */
    public function getIndex()
    {
        return $this->index;
    }
    
    /*
     * Демьянов А.Е.  07.09.2015
     * Явное получение адреса в виде строки
    */
    public function getAsString()
    {
        return $this->getCity() . " " . $this->getStreet() . " " . $this->getHouseNumber();
    }
    public function __toString()
    {
    	//return $this->getCity() . " " . $this->getStreet() . " " . $this->getHouseNumber();
        return $this->getAsString();
    }
}
