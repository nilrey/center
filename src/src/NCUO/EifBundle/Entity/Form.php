<?php

namespace App\NCUO\EifBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="FormRepository")
 * @ORM\Table(name="eif.forms") 
 */

class Form {
    
    /**
     * @ORM\Column(type="guid", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */ 
        
    protected $id_form;
    
    /**
     * @ORM\ManyToOne(targetEntity="Protocol", inversedBy="forms")
     * @ORM\JoinColumn(name="id_protocol", referencedColumnName="id_protocol")
     */     
    
    protected $protocol;
            
    /**
     * @ORM\Column(type="string", length=1000, nullable=false)
     */         
    
    protected $form_name;
        
    /**
     * @ORM\Column(type="text", nullable=true)
     */        
    
    protected $form_descr;    
    
    /**
     * @ORM\Column(type="datetime", nullable=false)
     */       
    
    protected $form_create_date;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */          
    
    protected $xml_xslt_protocol_file_import;      
    
    /**
     * @ORM\Column(type="integer", nullable=false)
     */     
    
    protected $data_act_control_interval;    
        
    /**
     * @ORM\OneToMany(targetEntity="FormField", mappedBy="form", fetch="EXTRA_LAZY")
     */
    
    protected $form_fields;
	
    /*
    // @ORM\OneToMany(targetEntity="\App\NCUO\NsiBundle\Entity\Classifier", mappedBy="classifier_form", fetch="EXTRA_LAZY")
     
    
    protected $classifiers;	
    */
 
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->form_fields = new \Doctrine\Common\Collections\ArrayCollection();
        //$this->classifiers = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id_form
     *
     * @return guid 
     */
    public function getIdForm()
    {
        return $this->id_form;
    }

    /**
     * Set form_name
     *
     * @param string $formName
     * @return Form
     */
    public function setFormName($formName)
    {
        $this->form_name = $formName;

        return $this;
    }

    /**
     * Get form_name
     *
     * @return string 
     */
    public function getFormName()
    {
        return $this->form_name;
    }

    /**
     * Set form_descr
     *
     * @param string $formDescr
     * @return Form
     */
    public function setFormDescr($formDescr)
    {
        $this->form_descr = $formDescr;

        return $this;
    }

    /**
     * Get form_descr
     *
     * @return string 
     */
    public function getFormDescr()
    {
        return $this->form_descr;
    }

    /**
     * Set form_create_date
     *
     * @param \DateTime $formCreateDate
     * @return Form
     */
    public function setFormCreateDate($formCreateDate)
    {
        $this->form_create_date = $formCreateDate;

        return $this;
    }

    /**
     * Get form_create_date
     *
     * @return \DateTime 
     */
    public function getFormCreateDate()
    {
        return $this->form_create_date;
    }

    /**
     * Set xml_xslt_protocol_file_import
     *
     * @param string $xmlXsltProtocolFileImport
     * @return Form
     */
    public function setXmlXsltProtocolFileImport($xmlXsltProtocolFileImport)
    {
        $this->xml_xslt_protocol_file_import = $xmlXsltProtocolFileImport;

        return $this;
    }

    /**
     * Get xml_xslt_protocol_file_import
     *
     * @return string 
     */
    public function getXmlXsltProtocolFileImport()
    {
        return $this->xml_xslt_protocol_file_import;
    }

    /**
     * Set data_act_control_interval
     *
     * @param integer $dataActControlInterval
     * @return Form
     */
    public function setDataActControlInterval($dataActControlInterval)
    {
        $this->data_act_control_interval = $dataActControlInterval;

        return $this;
    }

    /**
     * Get data_act_control_interval
     *
     * @return integer 
     */
    public function getDataActControlInterval()
    {
        return $this->data_act_control_interval;
    }

    /**
     * Set protocol
     *
     * @param \App\NCUO\EifBundle\Entity\Protocol $protocol
     * @return Form
     */
    public function setProtocol(\App\NCUO\EifBundle\Entity\Protocol $protocol = null)
    {
        $this->protocol = $protocol;

        return $this;
    }

    /**
     * Get protocol
     *
     * @return \App\NCUO\EifBundle\Entity\Protocol 
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * Add form_fields
     *
     * @param \App\NCUO\EifBundle\Entity\FormField $formFields
     * @return Form
     */
    public function addFormField(\App\NCUO\EifBundle\Entity\FormField $formFields)
    {
        $this->form_fields[] = $formFields;

        return $this;
    }

    /**
     * Remove form_fields
     *
     * @param \App\NCUO\EifBundle\Entity\FormField $formFields
     */
    public function removeFormField(\App\NCUO\EifBundle\Entity\FormField $formFields)
    {
        $this->form_fields->removeElement($formFields);
    }

    /**
     * Get form_fields
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFormFields()
    {
        return $this->form_fields;
    }

    /*
     * Add classifiers
     *
     * @param \App\NCUO\NsiBundle\Entity\Classifier $classifiers
     * @return Form
    public function addClassifier(\App\NCUO\NsiBundle\Entity\Classifier $classifiers)
    {
        $this->classifiers[] = $classifiers;

        return $this;
    }
     */

    /*
     * Remove classifiers
     *
     * @param \App\NCUO\NsiBundle\Entity\Classifier $classifiers
    public function removeClassifier(\App\NCUO\NsiBundle\Entity\Classifier $classifiers)
    {
        $this->classifiers->removeElement($classifiers);
    }
     */

    /*
     * Get classifiers
     *
     * @return \Doctrine\Common\Collections\Collection 
    public function getClassifiers()
    {
        return $this->classifiers;
    }
     */
}
