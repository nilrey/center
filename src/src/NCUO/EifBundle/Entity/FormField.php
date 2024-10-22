<?php

namespace App\NCUO\EifBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="FormFieldRepository")
 * @ORM\Table(name="eif.form_fields") 
 */

class FormField {
    
    /**
     * @ORM\Column(type="guid", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */ 
        
    protected $id_field;
    
    /**
     * @ORM\ManyToOne(targetEntity="Form", inversedBy="form_fields")
     * @ORM\JoinColumn(name="id_form", referencedColumnName="id_form")
     */     
    
    protected $form;
            
    /**
     * @ORM\Column(type="string", length=1000, nullable=false)
     */         
    
    protected $field_name;

    /**
     * @ORM\Column(type="string", length=1000, nullable=false)
     */         
    
    protected $field_pos;
    
    /**
     * @ORM\ManyToOne(targetEntity="Datatype", inversedBy="form_fields")
     * @ORM\JoinColumn(name="id_datatype", referencedColumnName="id_datatype")
     */     
    
    protected $datatype;    
     
    /**
     * @ORM\Column(type="integer", nullable=false)
     */     
    
    protected $key_flag;     
           

    /**
     * Get id_field
     *
     * @return guid 
     */
    public function getIdField()
    {
        return $this->id_field;
    }
	
    /**
     * Set id_field
     *
     * @return FormField 
     */
    public function setIdField($id_field)
    {
		$this->id_field = $id_field;
	
        return $this;
    }	

    /**
     * Set field_name
     *
     * @param string $fieldName
     * @return FormField
     */
    public function setFieldName($fieldName)
    {
        $this->field_name = $fieldName;

        return $this;
    }

    /**
     * Get field_name
     *
     * @return string 
     */
    public function getFieldName()
    {
        return $this->field_name;
    }

    /**
     * Set field_pos
     *
     * @param string $fieldPos
     * @return FormField
     */
    public function setFieldPos($fieldPos)
    {
        $this->field_pos = $fieldPos;

        return $this;
    }

    /**
     * Get field_pos
     *
     * @return string 
     */
    public function getFieldPos()
    {
        return $this->field_pos;
    }

    /**
     * Set key_flag
     *
     * @param integer $keyFlag
     * @return FormField
     */
    public function setKeyFlag($keyFlag)
    {
        $this->key_flag = $keyFlag;

        return $this;
    }

    /**
     * Get key_flag
     *
     * @return integer 
     */
    public function getKeyFlag()
    {
        return $this->key_flag;
    }

    /**
     * Set form
     *
     * @param \App\NCUO\EifBundle\Entity\Form $form
     * @return FormField
     */
    public function setForm(\App\NCUO\EifBundle\Entity\Form $form = null)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Get form
     *
     * @return \App\NCUO\EifBundle\Entity\Form 
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Set datatype
     *
     * @param \App\NCUO\EifBundle\Entity\Datatype $datatype
     * @return FormField
     */
    public function setDatatype(\App\NCUO\EifBundle\Entity\Datatype $datatype = null)
    {
        $this->datatype = $datatype;

        return $this;
    }

    /**
     * Get datatype
     *
     * @return \App\NCUO\EifBundle\Entity\Datatype 
     */
    public function getDatatype()
    {
        return $this->datatype;
    }
}
