<?php

namespace App\NCUO\OivBundle\Entity;

use App\NCUO\OivBundle\Entity\ORMObjectMetadata;
use Doctrine\ORM\Mapping as ORM;


/**
 * Field
 *
 * @ORM\Table(name="oivs_passports.oivs_pass_seсs_fields")
 * @ORM\Entity(repositoryClass="FieldRepository")
 */
class Field
{

    /**
     * @var string
     *
     * @ORM\Column(name="id_fld", type="string", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="oivs_passports.seq_oivs_pass_seсs_fields_id_fld", allocationSize=1, initialValue=1)
     */
    private $id_fld;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="text", nullable=true)
     */
    private $data;

    /**
     * @var DateTime 
     *
     * @ORM\Column(name="data_updated", type="string", nullable=false)
     */       
    
    private $data_updated;

    /**
     * @var string
     *
     * @ORM\Column(name="data_info_src", type="text", nullable=true)
     */
    private $data_info_src;

    /**
     * @var string
     *
     * @ORM\Column(name="id_sec", type="string", nullable=false)
     */
    private $id_sec;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id_fld_content_type", type="smallint")
     */
    private $id_fld_content_type;

    /**
     * @var integer
     *
     * @ORM\Column(name="view_order", type="smallint")
     */
    private $view_order;

    /**
     * @var string
     *
     * @ORM\Column(name="view_params", type="string", nullable=false)
     */
    private $view_params;

     /**
     * @var boolean
     *
     * @ORM\Column(name="editable", type="boolean", nullable=false)
     */  
    private $editable;

     /**
     * @var boolean
     *
     * @ORM\Column(name="auto_updatable", type="boolean", nullable=false)
     */  
    private $auto_updatable;

    /**
     * @var string
     *
     */
    public $id_fld_short;



    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id_fld;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Field
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
     * Set view_order
     *
     * @param string $view_order
     * @return Field
     */
    public function setViewOrder($view_order)
    {
        $this->view_order = $view_order;

        return $this;
    }

    /**
     * Get view_order
     *
     * @return integer 
     */
    public function getViewOrder()
    {
        return $this->view_order;
    }

    /**
     * Set editable
     *
     * @param string $editable
     * @return Field
     */
    public function setEditable($editable)
    {
        $this->editable = $editable;

        return $this;
    }

    /**
     * Get editable
     *
     * @return boolean 
     */
    public function getEditable()
    {
        return $this->editable;
    }

    /**
     * Set auto_updatable
     *
     * @param string $auto_updatable
     * @return Field
     */
    public function setAutoUpdatable($auto_updatable)
    {
        $this->auto_updatable = $auto_updatable;

        return $this;
    }

    /**
     * Get auto_updatable
     *
     * @return boolean 
     */
    public function getAutoUpdatable()
    {
        return $this->auto_updatable;
    }


    /**
     * Set id_sec
     *
     * @param string $id_sec
     * @return Field
     */
    public function setIdSec($id_sec)
    {
        $this->id_sec = $id_sec;

        return $this;
    }

    /**
     * Get id_sec
     *
     * @return string 
     */
    public function getIdSec()
    {
        return $this->id_sec;
    }


    /**
     * Set data
     *
     * @param string $data
     * @return Field
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return string 
     */
    public function getData()
    {
        return $this->data;
    }


    /**
     * Set data
     *
     * @param string $data_updated
     * @return Field
     */
    public function setDataUpdated($data_updated)
    {
        $this->data_updated = $data_updated;

        return $this;
    }

    /**
     * Get data_updated
     *
     * @return string 
     */
    public function getDataUpdated()
    {
        return $this->data_updated;
    }


    /**
     * Set data_info_src
     *
     * @param string $data_info_src
     * @return Field
     */
    public function setDataInfoSrc($data_info_src)
    {
        $this->data_info_src = $data_info_src;

        return $this;
    }

    /**
     * Get data_info_src
     *
     * @return string 
     */
    public function getDataInfoSrc()
    {
        return $this->data_info_src;
    }

    /**
     * Set id_fld_content_type
     *
     * @param string $id_fld_content_type
     * @return Field
     */
    public function setIdFldContentType($id_fld_content_type)
    {
        $this->id_fld_content_type = $id_fld_content_type;

        return $this;
    }

    /**
     * Get id_fld_content_type
     *
     * @return integer 
     */
    public function getIdFldContentType()
    {
        return $this->id_fld_content_type;
    }


    /**
     * Set view_params
     *
     * @param string $view_params
     * @return Field
     */
    public function setViewParams($view_params)
    {
        $this->view_params = $view_params;

        return $this;
    }

    /**
     * Get view_params
     *
     * @return string 
     */
    public function getViewParams()
    {
        return $this->view_params;
    }

    /**
     * Get id_fld_short
     *
     * @return string 
     */
    public function getIdFldShort()
    {
        $arIds = explode('__', $this->getId());
        return $arIds[2];
    }

    /**
     * Get getNoUserImgBase64
     *
     * @return string 
     */
    public function getNoUserImgBase64(){
        $img = '<img src=" data:image/jpeg;base64,/9j/4QAYRXhpZgAASUkqAAgAAAAAAAAAAAAAAP/sABFEdWNreQABAAQAAABkAAD/4QMtaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLwA8P3hwYWNrZXQgYmVnaW49Iu+7vyIgaWQ9Ilc1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCI/PiA8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIiB4OnhtcHRrPSJBZG9iZSBYTVAgQ29yZSA1LjMtYzAxMSA2Ni4xNDU2NjEsIDIwMTIvMDIvMDYtMTQ6NTY6MjcgICAgICAgICI+IDxyZGY6UkRGIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+IDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bXA6Q3JlYXRvclRvb2w9IkFkb2JlIFBob3Rvc2hvcCBDUzYgKE1hY2ludG9zaCkiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NTQxMjY2MjA0REE3MTFFMjhDMTk4RDU4NjFGNTFBNjAiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NTQxMjY2MjE0REE3MTFFMjhDMTk4RDU4NjFGNTFBNjAiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo1NDEyNjYxRTREQTcxMUUyOEMxOThENTg2MUY1MUE2MCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo1NDEyNjYxRjREQTcxMUUyOEMxOThENTg2MUY1MUE2MCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/Pv/uAA5BZG9iZQBkwAAAAAH/2wCEAAEBAQEBAQEBAQEBAQEBAQEB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">';
        return $img;
    }

    
    
}
