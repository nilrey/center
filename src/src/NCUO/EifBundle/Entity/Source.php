<?php

namespace App\NCUO\EifBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="SourceRepository")
 * @ORM\Table(name="eif.sources") 
 */

class Source {
    
    /**
     * @ORM\Column(type="guid", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */    
    
    protected $id_source;
    
    /**
     * @ORM\Column(type="string", length=1000, nullable=false)
     */       
    
    protected $source_name;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */        
    
    protected $source_descr;
    
    /**
     * @ORM\Column(type="datetime", nullable=false)
     */      
    
    protected $source_create_date;
    
    /**
     * @ORM\Column(type="string", length=4, nullable=false)
     */       
    
    protected $foiv_roiv_flag;    
    
    /**
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\Foiv", inversedBy="sources")
     * @ORM\JoinColumn(name="id_foiv", referencedColumnName="id")
     */    
    
    protected $foiv;
    
    /*
     * @ORM\ManyToOne(targetEntity="App\NCUO\FoivBundle\Entity\Roiv", inversedBy="sources")
     * @ORM\JoinColumn(name="id_roiv", referencedColumnName="id")
    
    protected $roiv;    
     */    
        
    /**
     * @ORM\OneToMany(targetEntity="Protocol", mappedBy="source", fetch="EXTRA_LAZY")
     */
    
    protected $protocols;

            
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->protocols = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id_source
     *
     * @return guid 
     */
    public function getIdSource()
    {
        return $this->id_source;
    }
	
    /**
     * Set id_source
     *
     * @return Source 
     */
    public function setIdSource($id_source)
    {
        $this->id_source = $id_source;
		
		return $this;
    }	

    /**
     * Set source_name
     *
     * @param string $sourceName
     * @return Source
     */
    public function setSourceName($sourceName)
    {
        $this->source_name = $sourceName;

        return $this;
    }

    /**
     * Get source_name
     *
     * @return string 
     */
    public function getSourceName()
    {
        return $this->source_name;
    }

    /**
     * Set source_descr
     *
     * @param string $sourceDescr
     * @return Source
     */
    public function setSourceDescr($sourceDescr)
    {
        $this->source_descr = $sourceDescr;

        return $this;
    }

    /**
     * Get source_descr
     *
     * @return string 
     */
    public function getSourceDescr()
    {
        return $this->source_descr;
    }

    /**
     * Set source_create_date
     *
     * @param \DateTime $sourceCreateDate
     * @return Source
     */
    public function setSourceCreateDate($sourceCreateDate)
    {
        $this->source_create_date = $sourceCreateDate;

        return $this;
    }

    /**
     * Get source_create_date
     *
     * @return \DateTime 
     */
    public function getSourceCreateDate()
    {
        return $this->source_create_date;
    }

    /*
     * Set foiv_roiv_flag
     *
     * @param string $foivRoivFlag
     * @return Source
    public function setFoivRoivFlag($foivRoivFlag)
    {
        $this->foiv_roiv_flag = $foivRoivFlag;

        return $this;
    }
     */

    /*
     * Get foiv_roiv_flag
     *
     * @return string 
    public function getFoivRoivFlag()
    {
        return $this->foiv_roiv_flag;
    }
     */

    /**
     * Set foiv
     *
     * @return Source
     */
    public function setFoiv( $foiv = null)
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
     * Set roiv
     *
     * @return Source
     */
    public function setRoiv( $roiv = null)
    {
        $this->roiv = $roiv;

        return $this;
    }

    /**
     * Get roiv
     *
     * @return App\NCUO\FoivBundle\Entity\Roiv 
     */
    public function getRoiv()
    {
        return $this->roiv;
    }

    /**
     * Add protocols
     *
     * 
     * @return Source
     */
    public function addProtocol( $protocols)
    {
        $this->protocols[] = $protocols;

        return $this;
    }

    /**
     * Remove protocols
     *
     */
    public function removeProtocol(  $protocols)
    {
        $this->protocols->removeElement($protocols);
    }

    /**
     * Get protocols
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProtocols()
    {
        return $this->protocols;
    }
}
