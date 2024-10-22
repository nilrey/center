<?php

namespace App\NCUO\EifBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="DatatypeRepository")
 * @ORM\Table(name="eif.datatypes") 
 */

class Datatype {
    
    /**
     * @ORM\Column(type="guid", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */ 
        
    protected $id_datatype;
                
    /**
     * @ORM\Column(type="string", length=1000, nullable=false)
     */         
    
    protected $datatype_name;
    
    /**
     * @ORM\Column(type="string", length=1000, nullable=false)
     */         
    
    protected $datatype_pgsql;    
    
    /**
     * @ORM\Column(type="integer", nullable=false)
     */      
    
    protected $sort_order;
                 
    /**
     * @ORM\OneToMany(targetEntity="FormField", mappedBy="form", fetch="EXTRA_LAZY")
     */
    
    protected $form_fields;
    
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->form_fields = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id_datatype
     *
     * @return guid 
     */
    public function getIdDatatype()
    {
        return $this->id_datatype;
    }

    /**
     * Set datatype_name
     *
     * @param string $datatypeName
     * @return Datatype
     */
    public function setDatatypeName($datatypeName)
    {
        $this->datatype_name = $datatypeName;

        return $this;
    }

    /**
     * Get datatype_name
     *
     * @return string 
     */
    public function getDatatypeName()
    {
        return $this->datatype_name;
    }

    /**
     * Set datatype_pgsql
     *
     * @param string $datatypePgsql
     * @return Datatype
     */
    public function setDatatypePgsql($datatypePgsql)
    {
        $this->datatype_pgsql = $datatypePgsql;

        return $this;
    }

    /**
     * Get datatype_pgsql
     *
     * @return string 
     */
    public function getDatatypePgsql()
    {
        return $this->datatype_pgsql;
    }

    /**
     * Set sort_order
     *
     * @param integer $sortOrder
     * @return Datatype
     */
    public function setSortOrder($sortOrder)
    {
        $this->sort_order = $sortOrder;

        return $this;
    }

    /**
     * Get sort_order
     *
     * @return integer 
     */
    public function getSortOrder()
    {
        return $this->sort_order;
    }

    /**
     * Add form_fields
     *
     * @param \App\NCUO\EifBundle\Entity\FormField $formFields
     * @return Datatype
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
}
