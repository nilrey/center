<?php
namespace App\NCUO\FoivBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use App\NCUO\FoivBundle\Entity\Foiv;
use App\NCUO\FoivBundle\Entity\Roiv;
use App\NCUO\FoivBundle\Entity\RoivFdo;
use App\NCUO\FoivBundle\Form\FoivType;
use App\NCUO\FoivBundle\Entity\FoivFdoPersons;
use App\NCUO\FoivBundle\Entity\RoivFdoPersons;

/**
 * ROIV controller.
  */
class ROIVController extends Controller
{
     /**
     * Lists all Foiv entities.
     *
     * @Route("/{foivid}/roiv", name="roiv")
     * @Method("GET")
     * @Template("ncuofoiv/ROIV/index.html.twig")
     */
    public function indexAction($foivid)
    {
        $em = $this->getDoctrine()->getManager();

        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);

        if (!$foiv) {
            throw $this->createNotFoundException('Unable to find Foiv entity.');
        }
        
        $entities = $em->getRepository('NCUOFoivBundle:FoivFdo')->findBy( array("foivFk" => $foivid ), array("id" => "desc"), 5);
        
        return array(
            'foiv' => $foiv,
            'entities' => $entities,
            'createURL'   => $this->generateUrl('foiv_roiv_new', array('foivid' => $foiv->getId() ) ),
        );
      
    }
    
    /**
     * Displays a form to create a new FoivFdo entity.
     *
     * @Route("/{foivid}/roiv/{id}/view", name="foiv_roiv_view")
     * @Method("GET")
     * @Template("ncuofoiv/ROIV/view.html.twig")
     */
    public function viewRecord($foivid, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
        if (!$foiv) {
            throw $this->createNotFoundException('Unable to find Foiv entity.');
        }
        $entity = $em->getRepository('NCUOFoivBundle:FoivFdo')->find($id);
        
        $personsList = $em->getRepository('NCUOFoivBundle:FoivFdoPersons')->findBy( array('fkFoiv'=>$foivid), array('fio'=>'asc') );
        
        return array(
            'foiv' => $foiv,
            'entity' => $entity,
            'personsList'  => $personsList,
            'urlCreate'   => $this->generateUrl('foiv_roiv_edit', array('foivid'=>$foiv->getId(), 'id'=> 0 ) ),
            'urlBack'   => $this->generateUrl('roiv', array('foivid' => $foiv->getId() ) ),
            'urlEdit'   => $this->generateUrl('foiv_roiv_edit', array('foivid'=>$foiv->getId(), 'id'=> $entity->getId() ) ),
        );
    }
    
    /**
     * Displays a form to create a new FoivFdo entity.
     *
     * @Route("/{foivid}/roiv/0/edit", name="foiv_roiv_new")
     * @Method("GET")
     * @Template("ncuofoiv/ROIV/edit.html.twig")
     */
    public function newForm($foivid)
    {
        $em = $this->getDoctrine()->getManager();
        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
        if (!$foiv) {
            throw $this->createNotFoundException('Unable to find Foiv entity.');
        }
        $entity = new RoivFdo();
        
        $personsList = $em->getRepository('NCUOFoivBundle:FoivFdoPersons')->findBy( array('fkFoiv'=>$foivid), array('fio'=>'asc') );
        
        return array(
            'foiv' => $foiv,
            'entity' => $entity,
            'personsList'  => $personsList,
            'urlNewPerson'   => $this->generateUrl('foiv_roiv_add_person', array('foivid' => $foiv->getId(), 'id' => 0 ) ),
            'urlCreate'   => $this->generateUrl('foiv_roiv_new', array('foivid' => $foiv->getId() ) ),
            'urlAction'   => $this->generateUrl('foiv_roiv_create', array('foivid' => $foiv->getId() ) ),
            'urlBack'   => $this->generateUrl('roiv', array('foivid' => $foiv->getId() ) ),
        );
    }
    
    /**
     * Displays a form to create a new FoivFdo entity.
     *
     * @Route("/{foivid}/roiv/0/edit", name="foiv_roiv_create")
     * @Method("POST")
     * @Template("ncuofoiv/ROIV/edit.html.twig")
     */
    public function createNewRecord(Request $request, $foivid)
    {
        $em = $this->getDoctrine()->getManager();
        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
        if (!$foiv) {
            throw $this->createNotFoundException('Unable to find Foiv entity.');
        }
        $supervisor = $em->getRepository('NCUOFoivBundle:FoivFdoPersons')->find( $request->request->get("supervisor") );
        $entity = new FoivFdo();
        
        $entity->setName($request->request->get("name" ));
        $entity->setShortName($request->request->get("shortName" ));
        $entity->setWebsite($request->request->get("website" ));
        $entity->setAddress($request->request->get("address"));
        $entity->setPhone($request->request->get("phone"));
        $entity->setEmail($request->request->get("email"));
        $entity->setSupervisorFk($supervisor);
        $entity->setFoivFk($foiv);


        try{
            $em->persist($entity);
            $em->flush();

            $this->addFlash(
                'notice',
                'Record created! Record #'.$entity->getId()
            );  
            
        }catch(Exception $e){
            $message = array(
                'message_error' => true,
                'message_text'  => $e->getMessage(),
                'error_explain'  => $e->getTraceAsString()
            );
        }
        
        return $this->redirect($this->generateUrl('foiv_roiv_edit', array('foivid'=>$foiv->getId(), 'id'=> $entity->getId() ) ) );
    }
    
    /**
     * Displays a form to create a new FoivFdo entity.
     *
     * @Route("/{foivid}/roiv/{id}/edit", name="foiv_roiv_edit")
     * @Method("GET")
     * @Template("ncuofoiv/ROIV/edit.html.twig")
     */
    public function editForm($foivid, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
        if (!$foiv) {
            throw $this->createNotFoundException('Unable to find Foiv entity.');
        }
        $entity = $em->getRepository('NCUOFoivBundle:FoivFdo')->find($id);
        
        $personsList = $em->getRepository('NCUOFoivBundle:FoivFdoPersons')->findBy( array('fkFoiv'=>$foivid), array('fio'=>'asc') );
        
        return array(
            'foiv' => $foiv,
            'entity' => $entity,
            'personsList'  => $personsList,
            'urlNewPerson'   => $this->generateUrl('foiv_roiv_add_person', array('foivid' => $foiv->getId(), 'id' => $entity->getId() ) ),
            'createURL'   => $this->generateUrl('foiv_roiv_edit', array('foivid'=>$foiv->getId(), 'id'=> 0 ) ),
            'urlAction'   => $this->generateUrl('foiv_roiv_edit', array('foivid'=>$foiv->getId(), 'id'=> $entity->getId() ) ),
            'urlBack'   => $this->generateUrl('roiv', array('foivid' => $foiv->getId() ) ),
            'urlView'   => $this->generateUrl('foiv_roiv_view', array('foivid'=>$foiv->getId(), 'id'=> $entity->getId() ) ),
        );
    }
    
    /**
     * Displays a form to create a new FoivFdo entity.
     *
     * @Route("/{foivid}/roiv/{id}/edit", name="foiv_roiv_update")
     * @Method("POST")
     * @Template("ncuofoiv/ROIV/edit.html.twig")
     */
    public function updateRecord(Request $request, $foivid, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
        if (!$foiv) {
            throw $this->createNotFoundException('Unable to find Foiv entity.');
        }
        $supervisor = $em->getRepository('NCUOFoivBundle:FoivFdoPersons')->find( $request->request->get("supervisor") );
        $entity = $em->getRepository('NCUOFoivBundle:FoivFdo')->find($id);
        
        $entity->setName($request->request->get("name" ));
        $entity->setShortName($request->request->get("shortName" ));
        $entity->setWebsite($request->request->get("website" ));
        $entity->setAddress($request->request->get("address"));
        $entity->setPhone($request->request->get("phone"));
        $entity->setEmail($request->request->get("email"));
        $entity->setSupervisorFk($supervisor);
        $entity->setFoivFk($foiv);

        try{
            $em->flush();

            $this->addFlash(
                'notice',
                'Record updated!'
            );                        
            
        }catch(Exception $e){
             $this->addFlash(
                'notice',
                'Error! Record not updated!'
            );  
        }
        
        return $this->redirect($this->generateUrl('foiv_roiv_edit', array('foivid'=>$foiv->getId(), 'id'=> $entity->getId() ) ) );
    }
    
    /**
     * Displays a form to create a new FoivFdo entity.
     *
     * @Route("/{foivid}/roiv/{id}/delete", name="foiv_roiv_delete")
     * @Method("POST")
     * @Template("ncuofoiv/ROIV/index.html.twig")
     */
    public function deleteRecord($id)
    {

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('NCUOFoivBundle:FoivFdo')->find($id);
        
            if (!$entity)
            {
                throw $this->createNotFoundException('Foiv not found');
            }
        try{
            $em->remove($entity);
            $em->flush();
            
            $resp['err_id']  = 0;
            $resp['res_msg'] = 'Record deleted!';
            
        }catch(Exception $e){
            $message = array(
                'message_error' => true,
                'message_text'  => $e->getMessage(),
                'error_explain'  => $e->getTraceAsString()
            );
        }
        
        //$response = new Response();
        //$response->setContent( json_encode( $message) );
        //
        //return $response;
        return new Response(Response::HTTP_OK );
    }

    /**
     * @Route("/{foivid}/roiv/{id}/newPerson", name = "foiv_roiv_add_person")
     * 
     * Create new roiv person
     */    
    public function newPerson(Request $request, $foivid, $id) {
        
        $resp = array('err_id' => NULL, 'res_msg' => NULL);

        try{
            if( trim($request->request->get("name")) == ""){
                $error_field_name = "name";
            }elseif(trim($request->request->get("position")) == ""){
                $error_field_name = "position";
            }elseif(trim($request->request->get("phone")) == ""){
                $error_field_name = "phone";
            }else{
                $error_field_name = "";
            }
            if( !empty($error_field_name)) {
                    $resp['err_id']  = 2;
                    $resp['res_msg'] = 'Please, fill all requered fields!';
                    $resp['content']["id"] = $error_field_name;
            }else{
    
                $em = $this->getDoctrine()->getManager();
                $entity = new FoivFdoPersons();
                $entity->setFio( $request->request->get("name") );
                $entity->setPosition($request->request->get("position"));
                $entity->setPhone($request->request->get("phone"));
                //$entity->setWebsiteUrl($request->request->get(''));
                $entity->setAddress($request->request->get('address'));
                $entity->setEmail($request->request->get('email'));
                $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
                if (!$foiv) {
                    $resp['err_id']  = 1;
                    $resp['res_msg'] = 'Foiv not found';
                }else{
            
                    $entity->setFkFoiv($foiv);
              
                    $em->persist($entity);
                    $em->flush();
                    if($entity->getId() > 0){
                        $resp['err_id']  = 0;
                        $resp['res_msg'] = $this->container->getParameter('msg.created');
                        $resp['content']["id"] = $entity->getId();
                        $resp['content']["name"] = $entity->getFio();
                    }else{
                        $resp['err_id']  = 1;
                        $resp['res_msg'] = $this->container->getParameter('msg.error.crt');
                    }
                }
            }
        }catch(Exception $e){
            $resp = array(
                'err_id' => 1,
                'res_msg'  => $e->getMessage(),
                'error_explain'  => $e->getTraceAsString()
            );
        }
        
        
        $response = new Response();
        $response->setContent( json_encode( $resp) );

        return $response;
    }
    

}
?>