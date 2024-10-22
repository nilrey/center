<?php

namespace App\NCUO\EifBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="ProtocolRepository")
 * @ORM\Table(name="eif.protocols") 
 */

class Protocol {
    
    /**
     * @ORM\Column(type="guid", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */        
    
    protected $id_protocol;
    
    /**
     * @ORM\ManyToOne(targetEntity="Source", inversedBy="protocols")
     * @ORM\JoinColumn(name="id_source", referencedColumnName="id_source")
     */    
    
    protected $source;
    
    /**
     * @ORM\Column(type="string", length=1000, nullable=false)
     */     
    
    protected $protocol_name;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */      
    
    protected $protocol_descr;
    
    /**
     * @ORM\Column(type="date", nullable=false)
     */       
    
    protected $protocol_sign_date;    
    
    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     */         
    
    protected $protocol_file_mime_type;

    /**
     * @ORM\Column(type="text", nullable=true)
     */          
    
    protected $protocol_doc;     
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */          
    
    protected $protocol_xml_xsd;     
	
    /**
     * @ORM\Column(type="integer", nullable=false)
     */     
    
    protected $enable_migration;
    
	/**
	 * @ORM\OneToMany(targetEntity="File", mappedBy="protocol", fetch="EXTRA_LAZY")
	 */

	protected $files;

    /**
     * @ORM\OneToMany(targetEntity="Form", mappedBy="protocol", fetch="EXTRA_LAZY")
     */
    
    protected $forms;
    
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->files = new \Doctrine\Common\Collections\ArrayCollection();
        $this->forms = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id_protocol
     *
     * @return guid 
     */
    public function getIdProtocol()
    {
        return $this->id_protocol;
    }
	
    /**
     * Set id_protocol
     *
     * @return Protocol 
     */
    public function setIdProtocol($id_protocol)
    {
		$this->id_protocol = $id_protocol;
	
        return $this;
    }	

    /**
     * Set protocol_name
     *
     * @param string $protocolName
     * @return Protocol
     */
    public function setProtocolName($protocolName)
    {
        $this->protocol_name = $protocolName;

        return $this;
    }

    /**
     * Get protocol_name
     *
     * @return string 
     */
    public function getProtocolName()
    {
        return $this->protocol_name;
    }

    /**
     * Set protocol_descr
     *
     * @param string $protocolDescr
     * @return Protocol
     */
    public function setProtocolDescr($protocolDescr)
    {
        $this->protocol_descr = $protocolDescr;

        return $this;
    }

    /**
     * Get protocol_descr
     *
     * @return string 
     */
    public function getProtocolDescr()
    {
        return $this->protocol_descr;
    }

    /**
     * Set protocol_sign_date
     *
     * @param \DateTime $protocolSignDate
     * @return Protocol
     */
    public function setProtocolSignDate($protocolSignDate)
    {
        $this->protocol_sign_date = $protocolSignDate;

        return $this;
    }

    /**
     * Get protocol_sign_date
     *
     * @return \DateTime 
     */
    public function getProtocolSignDate()
    {
        return $this->protocol_sign_date;
    }

    /**
     * Set protocol_file_mime_type
     *
     * @param string $protocolFileMimeType
     * @return Protocol
     */
    public function setProtocolFileMimeType($protocolFileMimeType)
    {
        $this->protocol_file_mime_type = $protocolFileMimeType;

        return $this;
    }

    /**
     * Get protocol_file_mime_type
     *
     * @return string 
     */
    public function getProtocolFileMimeType()
    {
        return $this->protocol_file_mime_type;
    }

    /**
     * Set protocol_doc
     *
     * @param string $protocolDoc
     * @return Protocol
     */
    public function setProtocolDoc($protocolDoc)
    {
        $this->protocol_doc = $protocolDoc;

        return $this;
    }

    /**
     * Get protocol_doc
     *
     * @return string 
     */
    public function getProtocolDoc()
    {
        return $this->protocol_doc;
    }

    /**
     * Set protocol_xml_xsd
     *
     * @param string $protocolXmlXsd
     * @return Protocol
     */
    public function setProtocolXmlXsd($protocolXmlXsd)
    {
        $this->protocol_xml_xsd = $protocolXmlXsd;

        return $this;
    }

    /**
     * Get protocol_xml_xsd
     *
     * @return string 
     */
    public function getProtocolXmlXsd()
    {
        return $this->protocol_xml_xsd;
    }

    /**
     * Set enable_migration
     *
     * @param integer $enableMigration
     * @return File
     */
    public function setEnableMigration($enableMigration)
    {
        $this->enable_migration = $enableMigration;

        return $this;
    }

    /**
     * Get enable_migration
     *
     * @return integer 
     */
    public function getEnableMigration()
    {
        return $this->enable_migration;
    }	
	
    /**
     * Set source
     *
     * @param \App\NCUO\EifBundle\Entity\Source $source
     * @return Protocol
     */
    public function setSource(\App\NCUO\EifBundle\Entity\Source $source = null)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return \App\NCUO\EifBundle\Entity\Source 
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Add files
     *
     * @param \App\NCUO\EifBundle\Entity\File $files
     * @return Protocol
     */
    public function addFile(\App\NCUO\EifBundle\Entity\File $files)
    {
        $this->files[] = $files;

        return $this;
    }

    /**
     * Remove files
     *
     * @param \App\NCUO\EifBundle\Entity\File $files
     */
    public function removeFile(\App\NCUO\EifBundle\Entity\File $files)
    {
        $this->files->removeElement($files);
    }

    /**
     * Get files
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Add forms
     *
     * @param \App\NCUO\EifBundle\Entity\Form $forms
     * @return Protocol
     */
    public function addForm(\App\NCUO\EifBundle\Entity\Form $forms)
    {
        $this->forms[] = $forms;

        return $this;
    }

    /**
     * Remove forms
     *
     * @param \App\NCUO\EifBundle\Entity\Form $forms
     */
    public function removeForm(\App\NCUO\EifBundle\Entity\Form $forms)
    {
        $this->forms->removeElement($forms);
    }

    /**
     * Get forms
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getForms()
    {
        return $this->forms;
    }
}
