<?php

namespace App\NCUO\OivBundle\Entity;

use App\NCUO\OivBundle\Entity\ORMObjectMetadata;
use Doctrine\ORM\Mapping as ORM;


/**
 * Oiv
 *
 * @ORM\Table(name="oivs_passports.oivs_pass_sections")
 * @ORM\Entity(repositoryClass="SectionRepository")
 */
class Section
{

    /**
     * @var string
     *
     * @ORM\Column(name="id_sec", type="string", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="oivs_passports.seq_oivs_pass_sections_id_sec", allocationSize=1, initialValue=1)
     */
    private $id_sec;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="view_order", type="smallint")
     */
    
    private $view_order;

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
     * @var boolean
     *
     * @ORM\Column(name="show_as_menu_item", type="boolean", nullable=false)
     */  
    private $show_as_menu_item;

    /**
     * @var string
     *
     * @ORM\Column(name="id_oiv", type="string", nullable=false)
     */
    private $id_oiv;
    
    /**
     * @var string
     *
     * @ORM\Column(name="id_parent_sec", type="string", nullable=false)
     */
    private $id_parent_sec;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id_sec_view_type", type="smallint")
     */
    private $id_sec_view_type;

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
        return $this->id_oiv;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Section
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
     * @return Section
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
     * @return Section
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
     * @return Section
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
     * Set show_as_menu_item
     *
     * @param string $show_as_menu_item
     * @return Section
     */
    public function setShowAsMenuItem($show_as_menu_item)
    {
        $this->show_as_menu_item = $show_as_menu_item;

        return $this;
    }

    /**
     * Get show_as_menu_item
     *
     * @return boolean 
     */
    public function getShowAsMenuItem()
    {
        return $this->show_as_menu_item;
    }


    /**
     * Set id_oiv
     *
     * @param string $id_oiv
     * @return Section
     */
    public function setIdOiv($id_oiv)
    {
        $this->id_oiv = $id_oiv;

        return $this;
    }

    /**
     * Get id_oiv
     *
     * @return string 
     */
    public function getIdOiv()
    {
        return $this->id_oiv;
    }


    /**
     * Set id_parent_sec
     *
     * @param string $id_parent_sec
     * @return Section
     */
    public function setIdParentSec($id_parent_sec)
    {
        $this->id_parent_sec = $id_parent_sec;

        return $this;
    }

    /**
     * Get id_parent_sec
     *
     * @return string 
     */
    public function getIdParentSec()
    {
        return $this->id_parent_sec;
    }


    /**
     * Set id_sec_view_type
     *
     * @param string $id_sec_view_type
     * @return Section
     */
    public function setIdSecViewType($id_sec_view_type)
    {
        $this->id_sec_view_type = $id_sec_view_type;

        return $this;
    }

    /**
     * Get id_sec_view_type
     *
     * @return integer 
     */
    public function getIdSecViewType()
    {
        return $this->id_sec_view_type;
    }

}
