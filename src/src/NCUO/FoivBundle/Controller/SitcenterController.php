<?php

namespace App\NCUO\FoivBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use App\NCUO\FoivBundle\Entity\FoivSitcenter;
use App\NCUO\FoivBundle\Form\FoivSitcenterType;
use App\NCUO\FoivBundle\Entity\SitcenterPerson;
use App\NCUO\FoivBundle\Entity\FoivPerson;

/**
 * Sitcenter controller.
 *
 * @Route("/sitcenter")
 */
class SitcenterController extends Controller
{

    /**
     * Lists all FoivSitcenter entities.
     *
     * @Route("/{foivid}/sitcenter", name="foiv_sitcenter")
     * @Method("GET")
     * @Template("ncuofoiv/Sitcenter/index.html.twig")
     */
    public function indexAction($foivid)
    {
        $em = $this->getDoctrine()->getManager();

        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);

        if (!$foiv) {
            throw $this->createNotFoundException('Unable to find Foiv entity.');
        }

        //$foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
        $entities = $em->getRepository('NCUOFoivBundle:FoivSitcenter')->findBy( array( 'foiv' => $foivid ), array('name'=>'asc') );

        return array(
            'foiv' => $foiv,
            'entities' => $entities,
            'createURL'   => $this->generateUrl('foiv_sitcenter_new', array('foivid' => $foiv->getId() ) ),
        );
    }
    
    /**
     * Displays a form to create a new FoivSitcenter entity.
     *
     * @Route("/{foivid}/sitcenter/{id}/view", name="foiv_sitcenter_view")
     * @Method("GET")
     * @Template("ncuofoiv/Sitcenter/view.html.twig")
     */
    public function viewRecord($foivid, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
        if (!$foiv) {
            throw $this->createNotFoundException('Unable to find Foiv entity.');
        }
        $entity = $em->getRepository('NCUOFoivBundle:FoivSitcenter')->find($id);
        
        $personsList = $em->getRepository('NCUOFoivBundle:SitcenterPerson')->findBy( array('sitcenterId'=>$id), array('fio'=>'asc') );
        
        return array(
            'foiv' => $foiv,
            'entity' => $entity,
            'personsList'  => $personsList,
            'urlCreate'   => $this->generateUrl('foiv_sitcenter_edit', array('foivid'=>$foiv->getId(), 'id'=> 0 ) ),
            'urlBack'   => $this->generateUrl('foiv_sitcenter', array('foivid' => $foiv->getId() ) ),
            'urlEdit'   => $this->generateUrl('foiv_sitcenter_edit', array('foivid'=>$foiv->getId(), 'id'=> $entity->getId() ) ),
        );
    }
    
    /**
     * Displays a form to create a new FoivSitcenter entity.
     *
     * @Route("/{foivid}/sitcenter/0/edit", name="foiv_sitcenter_new")
     * @Method("GET")
     * @Template("ncuofoiv/Sitcenter/edit.html.twig")
     */
    public function newForm($foivid)
    {
        $em = $this->getDoctrine()->getManager();
        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
        if (!$foiv) {
            throw $this->createNotFoundException('Unable to find Foiv entity.');
        }
        $entity = new FoivSitcenter();
        
        $personsList = $em->getRepository('NCUOFoivBundle:SitcenterPerson')->findBy( array('sitcenterId'=>$foivid), array('fio'=>'asc') );
        
        return array(
            'foiv' => $foiv,
            'entity' => $entity,
            'personsList'  => $personsList,
            'urlNewPerson'   => $this->generateUrl('foiv_sitcenter_add_person', array('foivid' => $foiv->getId(), 'id' => 0 ) ),
            'urlCreate'   => $this->generateUrl('foiv_sitcenter_new', array('foivid' => $foiv->getId() ) ),
            'urlAction'   => $this->generateUrl('foiv_sitcenter_create', array('foivid' => $foiv->getId() ) ),
            'urlBack'   => $this->generateUrl('foiv_sitcenter', array('foivid' => $foiv->getId() ) ),
        );
    }
    
    /**
     * Displays a form to create a new FoivSitcenter entity.
     *
     * @Route("/{foivid}/sitcenter/0/edit", name="foiv_sitcenter_create")
     * @Method("POST")
     * @Template("ncuofoiv/Sitcenter/edit.html.twig")
     */
    public function createNewRecord(Request $request, $foivid)
    {
        $em = $this->getDoctrine()->getManager();
        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
        if (!$foiv) {
            throw $this->createNotFoundException('Unable to find Foiv entity.');
        }
        $director = $em->getRepository('NCUOFoivBundle:SitcenterPerson')->find( $request->request->get("director") );
        $entity = new FoivSitcenter();
        
        $entity->setName($request->request->get("name" ));
        $entity->setAddress($request->request->get("address"));
        $entity->setPhone($request->request->get("phone"));
        $entity->setEmail($request->request->get("email"));
        $entity->setWebSite($request->request->get("website" ));
        $entity->setFunctions($request->request->get("functions" ));
		
		if(!$director)
		{
			$entity->setDirector($director);
		}
		
        $entity->setFoiv($foiv);

        try{
            $em->persist($entity);
            $em->flush();

			//Демьянов А.Е. 01.02.2016
			//получаем ID сит.центра и фиксируем для персоны
			//Иначе при заргузке списка персон сит. центра для конкретного центра спиок будет пуст
			if(!director)
			{
				$director->setSitcenterId($entity->getId());
			}
			
			$em->flush();
			
            $this->addFlash(
                'notice',
                $this->container->getParameter('msg.created')
            );  
            
        }catch(Exception $e){
            $message = array(
                'message_error' => true,
                'message_text'  => $e->getMessage(),
                'error_explain'  => $e->getTraceAsString()
            );
        }
        
        return $this->redirect($this->generateUrl('foiv_sitcenter_edit', array('foivid'=>$foiv->getId(), 'id'=> $entity->getId() ) ) );
    }
    
    /**
     * Displays a form to create a new FoivSitcenter entity.
     *
     * @Route("/{foivid}/sitcenter/{id}/edit", name="foiv_sitcenter_edit")
     * @Method("GET")
     * @Template("ncuofoiv/Sitcenter/edit.html.twig")
     */
    public function editForm($foivid, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
        if (!$foiv) {
            throw $this->createNotFoundException('Unable to find Foiv entity.');
        }
        $entity = $em->getRepository('NCUOFoivBundle:FoivSitcenter')->find($id);
        
		$this->get('logger')->debug("call editForm Phone:".$entity->getPhone());
        $personsList = $em->getRepository('NCUOFoivBundle:SitcenterPerson')->findBy( array('sitcenterId'=>$id), array('fio'=>'asc') );
        
        return array(
            'foiv' => $foiv,
            'entity' => $entity,
            'personsList'  => $personsList,
            'urlNewPerson'   => $this->generateUrl('foiv_sitcenter_add_person', array('foivid' => $foiv->getId(), 'id' => $entity->getId() ) ),
            'createURL'   => $this->generateUrl('foiv_sitcenter_edit', array('foivid'=>$foiv->getId(), 'id'=> 0 ) ),
            'urlAction'   => $this->generateUrl('foiv_sitcenter_edit', array('foivid'=>$foiv->getId(), 'id'=> $entity->getId() ) ),
            'urlBack'   => $this->generateUrl('foiv_sitcenter', array('foivid' => $foiv->getId() ) ),
            'urlView'   => $this->generateUrl('foiv_sitcenter_view', array('foivid'=>$foiv->getId(), 'id'=> $entity->getId() ) ),
        );
    }
    
    /**
     * Displays a form to create a new FoivSitcenter entity.
     *
     * @Route("/{foivid}/sitcenter/{id}/edit", name="foiv_sitcenter_update")
     * @Method("POST")
     * @Template("ncuofoiv/Sitcenter/edit.html.twig")
     */
    public function updateRecord(Request $request, $foivid, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
        if (!$foiv) {
            throw $this->createNotFoundException('Unable to find Foiv entity.');
        }
        $director = $em->getRepository('NCUOFoivBundle:SitcenterPerson')->find( $request->request->get("director") );
        $entity = $em->getRepository('NCUOFoivBundle:FoivSitcenter')->find($id);
        
        $entity->setName($request->request->get("name" ));
        $entity->setAddress($request->request->get("address"));
		$this->get('logger')->debug("call upadteRecord Phone:".$request->request->get("phone"));
        $entity->setPhone($request->request->get("phone"));
        $entity->setEmail($request->request->get("email"));
        $entity->setWebSite($request->request->get("website" ));
        $entity->setFunctions($request->request->get("functions" ));
        $entity->setDirector($director);
        $entity->setFoiv($foiv);

        try{
            $em->flush();

            $this->addFlash(
                'notice',
                $this->container->getParameter('msg.updated')
            );                        
            
        }catch(Exception $e){
             $this->addFlash(
                'notice',
                $this->container->getParameter('msg.error.upd')
            );  
        }
        
        return $this->redirect($this->generateUrl('foiv_sitcenter_edit', array('foivid'=>$foiv->getId(), 'id'=> $entity->getId() ) ) );
    }
    
    /**
     * Displays a form to create a new FoivSitcenter entity.
     *
     * @Route("/{foivid}/sitcenter/{id}/delete", name="foiv_sitcenter_delete")
     * @Method("POST")
     * @Template("ncuofoiv/Sitcenter/index.html.twig")
     */
    public function deleteRecord($id)
    {

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('NCUOFoivBundle:FoivSitcenter')->find($id);
        
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
        
        return new Response(Response::HTTP_OK );
    }


    /**
     * @Route("/{foivid}/sitcenter/{id}/newPerson", name = "foiv_sitcenter_add_person")
     * 
     * Create new sitcenter person
     */    
    public function newPerson(Request $request, $foivid, $id) {
        
        $resp = array('err_id' => NULL, 'res_msg' => NULL);

        try{
			if( trim($request->request->get("surname")) == ""){
                $error_field_name = "surname";
            }else
            if( trim($request->request->get("name")) == ""){
                $error_field_name = "name";
            }else
			if( trim($request->request->get("patronimyc")) == ""){
                $error_field_name = "patronimyc";
            }/*else
			if(trim($request->request->get("position")) == ""){
                $error_field_name = "position";
            }elseif(trim($request->request->get("phone")) == ""){
                $error_field_name = "phone";
            }*/else{
                $error_field_name = "";
            }
            if( !empty($error_field_name)) {
                    $resp['err_id']  = 2;
                    $resp['res_msg'] = 'Пожалуйста, заполните требуемые поля!';
                    $resp['content']["id"] = $error_field_name;
            }else{
    
                $em = $this->getDoctrine()->getManager();
				
				//Демьянов А.Е. 01.02.2016 
				//Сначала создаем новую FOIV-персону и сохраняем ее в БД, 
				//а потом привязываем sitcenter-персону к новой персоне, 
				//т.к. сущность sitcenter зависит от FoivPerson
				$NewPerson = new FoivPerson();
				//$NewPerson->setFIO($request->request->get("name"));
				$NewPerson->setSurname($request->request->get("surname"));
				$NewPerson->setName($request->request->get("name"));
				$NewPerson->setPatronymic($request->request->get("patronimyc"));
				
				$NewPerson->setPosition($request->request->get("position"));
				$NewPerson->setPhone($request->request->get("phone"));
				$NewPerson->setEmail($request->request->get('email'));
				
				$em->persist($NewPerson);
                $em->flush();
				
                $entity = new SitcenterPerson();
                //$entity->setFio( $request->request->get("name") );
                //$entity->setPosition($request->request->get("position"));
                //$entity->setPhone($request->request->get("phone"));
                //$entity->setEmail($request->request->get('email'));
				//временно не устанавливаем значение на одно поле
				//$entity->setPhotoUrl();
				
                $entity->setSitcenterId( $id );
				
				
                
                $foiv = $em->getRepository('NCUOFoivBundle:Foiv')->find($foivid);
                
                if (!$foiv) {
                    $resp['err_id']  = 1;
                    $resp['res_msg'] = 'Foiv not found';
                }else{
            
					//Демьянов А.Е. 01.02.2016 
					$entity->setPerson($NewPerson);
					
                    $em->persist($entity);
                    $em->flush();
					
                    if($entity->getId() > 0){
                        $resp['err_id']  = 0;
                        $resp['res_msg'] = $this->container->getParameter('msg.created');
                        $resp['content']["id"] = $entity->getId();
                        $resp['content']["name"] = $entity->getFio();
						//$entity->setSitcenterId( $entity->getId() );
						
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
    
    
    // -------------------------------------------------------------------------------------------------------------------------------------------------------
    // -------------------------------------------------------------------------------------------------------------------------------------------------------
    // -------------------------------------------------------------------------------------------------------------------------------------------------------
    // -------------------------------------------------------------------------------------------------------------------------------------------------------
    // -------------------------------------------------------------------------------------------------------------------------------------------------------
    /**
     * Creates a new FoivSitcenter entity.
     *
     * @Route("/", name="foivsitcenter_new")
     * @Method("POST")
     * @Template("NCUOFoivBundle:FoivSitcenter:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new FoivSitcenter();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('foivsitcenter_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a FoivSitcenter entity.
     *
     * @param FoivSitcenter $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(FoivSitcenter $entity)
    {
        $form = $this->createForm(new FoivSitcenterType(), $entity, array(
            'action' => $this->generateUrl('foivsitcenter_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new FoivSitcenter entity.
     *
     * @Route("/new", name="foivsitcenter_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new FoivSitcenter();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a FoivSitcenter entity.
     *
     * @Route("/{id}", name="foivsitcenter_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NCUOFoivBundle:FoivSitcenter')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find FoivSitcenter entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing FoivSitcenter entity.
     *
     * @Route("/{id}/edit", name="foivsitcenter_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NCUOFoivBundle:FoivSitcenter')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find FoivSitcenter entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a FoivSitcenter entity.
    *
    * @param FoivSitcenter $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(FoivSitcenter $entity)
    {
        $form = $this->createForm(new FoivSitcenterType(), $entity, array(
            'action' => $this->generateUrl('foivsitcenter_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing FoivSitcenter entity.
     *
     * @Route("/{id}", name="foivsitcenter_update")
     * @Method("PUT")
     * @Template("NCUOFoivBundle:FoivSitcenter:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NCUOFoivBundle:FoivSitcenter')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find FoivSitcenter entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('foivsitcenter_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a FoivSitcenter entity.
     *
     * @Route("/{id}", name="foivsitcenter_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NCUOFoivBundle:FoivSitcenter')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find FoivSitcenter entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('foivsitcenter'));
    }

    /**
     * Creates a form to delete a FoivSitcenter entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('foivsitcenter_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
