<?php
namespace App\NCUO\FoivBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use App\NCUO\FoivBundle\Controller\ErrorResponseGenerator;
use App\NCUO\FoivBundle\Controller\SearchFoivPassport;
use App\NCUO\FoivBundle\Entity\Foiv;
use App\NCUO\PortalBundle\Entity\User;

/**
 * Foiv's Search Foiv Passport
 */
class FoivSearch extends Controller
{
    const ACCESS_FULL = "FULL";
    const ACCESS_LIMITED = "LIMITED";
    const ACCESS_DENY = "DENY";

    /**
     * Show Search Form
     *
     * @Route("/0/foiv_search", name="foiv_search")
     * @Template("ncuofoiv/FoivSearch/index.html.twig")
     */
    public function searchAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $textSearch = $request->request->get("textSearch");
        $userAccess = $this->getAccessType($user);
        $searchResult = array();
		$tmpResult = array("cntResult"=>"", "searchResult" => array(), "sql"=>"");
		$tmpResult2 = array("cntResult"=>"-1", "searchResult" => array(), "sql"=>"");
		
		$output = strtolower($textSearch);
        if(!empty($textSearch)):
            // search begin
			
			$tmpResult = $this->searchTableFoiv($userAccess, $textSearch);
            if($tmpResult["cntResult"] > 0 ){
				//array_push($searchResult, $tmpResult["searchDisplay"]);
				foreach( $tmpResult["searchDisplay"] as $arRow){
					$searchResult[] = $arRow;
				}
			}
			/*
			$tmpResult = $this->searchTableFoivDepartments($userAccess, $textSearch);
            if($tmpResult["cntResult"] > 0 ){
				foreach( $tmpResult["searchDisplay"] as $arRow){
					$searchResult[] = $arRow;
				}
			}
			*/
			
			//$tmpResult2 = $this->searchTableFoivDepartments2( "FoivDepartments", "foiv", array("name", "foiv"), array("name"), array(), $textSearch);
			
			//$tmpResult2 = $this->searchTableFoivDepartments2( "FoivPvo", "fk_foiv", array("name"), array("name"), array(), $textSearch);
			/*
			$tmpResult = $this->searchTableFoivDepartments($userAccess, $textSearch);
            if($tmpResult["cntResult"] > 0 ){
				foreach( $tmpResult["searchDisplay"] as $arRow){
					$searchResult[] = $arRow;
				}
			}
			*/
        endif;
            //$srch = new SearchFoivPassport();

        return array(
            "userAccess" => $userAccess,
            "textSearch" => $textSearch,
            "searchResult" => $searchResult,
			"isResult" => $tmpResult["cntResult"],
			"resDep" => $tmpResult2,
			"resDepPar" => serialize($tmpResult2["searchResult"]),
        );
    }
    
    /**
     * get Access Type
     *
     */
    private function getAccessType($user){
        $accessType = self::ACCESS_DENY;
        $foivList = array();
        if( in_array($user->getRole()->getId(), array(1, 2, 5)) ) { // ROLE_ADMIN, ROLE_NCUO, ROLE_VDL
            $accessType = self::ACCESS_FULL;
        }elseif( in_array($user->getRole()->getId(), array(3)) ){ // ROLE_FOIV
            $accessType = self::ACCESS_LIMITED;
            $foivList = array($user->getFoiv()->getId());
        }
        return array(
            "accessType" => $accessType,
            "foivList" => $foivList
        );
    }

    public function searchTableFoiv( $userAccess, $search_line ){
		$tableName = "Foiv";
		$fieldFoivIdName = "id";
		$fieldTitleName = "name";
		
        $tableFields = array(
            "name",
            "shortname",
			"sitename",
			"siteurl",
			"descriptionText",
			"basictasks",
			"sortOrder"
        );
		
        $searchFields = array(
            "name",
            "shortname",
			"siteurl",
			"sitename",
			//"basictasks",
			"descriptionText",
        );
		$urlTemplate = array(
			"urlFind" => array("0000"),		 
			"urlFieldReplace" => array("id"),		 
			"urlString" => "#",
		);
		$urlTemplate['urlString'] = $this->generateUrl('foiv_show', array('id'=> $urlTemplate["urlFind"][0] ));
		
		$prepareResult = $this->prepareSearchResults($userAccess, $search_line, $tableName, $tableFields, $searchFields, $fieldFoivIdName, $fieldTitleName, $urlTemplate);

        return $prepareResult;
    }
	
    public function searchTableFoivDepartments( $userAccess, $search_line ){
		$tableName = "FoivDepartments";
		$fieldFoivIdName = "id";
		$fieldTitleName = "name";
		
        $tableFields = array(
            "name",
			"foiv"
        );
        $searchFields = array(
            "name",
        );
		$urlTemplate = array(
			"urlFind" => array("48", "002"),		 
			"urlFieldReplace" => array("id"),		 
			"urlString" => "",
		);
		$urlTemplate['urlString'] = $this->generateUrl('foiv_department_view', array('foivid'=>$urlTemplate["urlFind"][0], 'id'=> $urlTemplate["urlFind"][1] ) );
		
		$prepareResult = $this->prepareSearchResults2($userAccess, $search_line, $tableName, $tableFields, $searchFields, $fieldFoivIdName, $fieldTitleName, $urlTemplate);

        return $prepareResult;
    }
	
    public function prepareSearchResults( $userAccess, $search_line, $tableName, $tableFields, $searchFields, $fieldFoivIdName, $fieldTitleName, $urlTemplate ){
		$search_line = strtolower($search_line);
		$responseResult["cntResult"] = 0;
        $responseResult["searchResult"] = array();
        $responseResult["searchDisplay"] = array();
		
		if( $userAccess["accessType"] === self::ACCESS_FULL || $userAccess["accessType"] === self::ACCESS_LIMITED ){
			$searchResult = $this->searchTable( $tableName, $fieldFoivIdName, $tableFields, $searchFields, $userAccess["foivList"], $search_line);
			$responseResult["cntResult"] = count($searchResult);
			
			if( $responseResult["cntResult"] > 0
			   && count($urlTemplate['urlFieldReplace']) > 0
			   && count($tableFields) > 0
			   ){
				$responseResult["searchResult"] = $searchResult;
				foreach( $searchResult as $arRow){
					foreach( $arRow as $field => $value ){
						if( in_array($field, $tableFields) ){
							$strPosition = strpos( $this->getLower($value) , $this->getLower($search_line) );
							if( $strPosition !== false ){
								$strStart = $strPosition ; //$strPosition-20 > 0 ? $strPosition-20 : 0 ;
								$strLength = mb_strlen($search_line, "UTF-8") + 100 ;
								$arrFieldReplace = array();
								foreach( $urlTemplate['urlFieldReplace'] as $tmpFieldReplace ){
									$arrFieldReplace[] = $arRow[ $tmpFieldReplace ];
								}
								$substr = substr( $this->getLower(strip_tags($value)), $strStart, $strLength );
								$substr = substr($substr, 0, strripos($substr, " ") );
								if(mb_strlen($substr ) > 0 ) $substr = "&hellip;".$substr."&hellip;";
								//"&hellip;".str_replace( substr( $this->getLower($value), $strStart, $strLength ), "<b><i>{$search_line}</i></b>", $this->getLower($value) )."&hellip;",
								$responseResult["searchDisplay"][] = array(
									"key"=> $arRow[$fieldFoivIdName],
									"name" => $arRow[$fieldTitleName],
									"content"=> $substr,
									"url" => str_replace( $urlTemplate['urlFind'], $arrFieldReplace, $urlTemplate['urlString'] )
								);
								break;
							}
						}
					}
				}
			}
		}

        return $responseResult;
	}
	
    public function prepareSearchResults2( $userAccess, $search_line, $tableName, $tableFields, $searchFields, $fieldFoivIdName, $fieldTitleName, $urlTemplate ){
		$search_line = strtolower($search_line);
		$responseResult["cntResult"] = 0;
        $responseResult["searchResult"] = array();
        $responseResult["searchDisplay"] = array();
		
		if( $userAccess["accessType"] === self::ACCESS_FULL || $userAccess["accessType"] === self::ACCESS_LIMITED ){
			$searchResult = $this->searchTable( $tableName, $fieldFoivIdName, $tableFields, $searchFields, $userAccess["foivList"], $search_line);
			$responseResult["cntResult"] = count($searchResult);
			
			if( $responseResult["cntResult"] > 0
			   && count($urlTemplate['urlFieldReplace']) > 0
			   && count($tableFields) > 0
			   ){
				$responseResult["searchResult"] = $searchResult;
				foreach( $searchResult as $arRow){
					foreach( $arRow as $field => $value ){
						if( in_array($field, $tableFields) ){
							$strPosition = strpos( $this->getLower($value) , $this->getLower($search_line) );
							if( $strPosition !== false ){
								$strStart = $strPosition-10 > 0 ? $strPosition-10 : 0 ;
								$strLength = strlen($search_line) + 10 ;
								$arrFieldReplace = array();
								foreach( $urlTemplate['urlFieldReplace'] as $tmpFieldReplace ){
									$arrFieldReplace[] = $arRow[ $tmpFieldReplace ];
								}
								
								$responseResult["searchDisplay"][] = array(
									"key"=> $arRow[$fieldFoivIdName],
									"name" => $arRow[$fieldTitleName],
									"content"=>
									//substr( $this->getLower($value), $strStart, $strLength ),
									"&hellip;".str_replace($this->getLower($search_line), "<b><i>{$search_line}</i></b>", $this->getLower($value) )."&hellip;",
									"url" => $urlTemplate['urlString']
								);
								break;
							}
						}
					}
				}
			}
		}

        return $responseResult;
	}
	
    public function searchTable( $tableName, $fieldFoivIdName, $tableFields, $searchFields, $foivId, $search_line)
    {
		$results = array();
		if( count($tableFields) > 0 ){
			$strTableFields = "t.id, t.".implode(", t.", $tableFields);
			$em = $this->getDoctrine()->getEntityManager();
			
			$search_like = "LOWER(t.".
			implode( ") LIKE LOWER('%".$search_line."%') OR LOWER(t.", $searchFields ).
			") LIKE LOWER('%".$search_line."%')";
			
			if( count($foivId) > 0 ){
				$query = $em->createQuery(
				'
				SELECT t FROM NCUOFoivBundle:'.$tableName.' t WHERE t.'.$fieldFoivIdName.' IN (:foivId) AND ('.$search_like.')
				'
				)
				->setParameter('foivId', implode(", ", $foivId) )
				;
			}else{
				$query = $em->createQuery(
				'
				SELECT '.$strTableFields.' FROM NCUOFoivBundle:'.$tableName.' t WHERE '.$search_like.'
				'
				)
				;
			}
		}
		$results = $query->getResult();

		return $results;
    }

    public function searchTableFoivDepartments2( $entitiyTableName, $fieldFoivIdName, $tableFields, $searchFields, $foivId, $search_line)
    {
		
		$results["sql"] = "none";
		$results["searchResult"] = array();
		$results["cntResult"] = -1;
		if( count($tableFields) > 0 ){
			$strTableFields = "t.id, t.".implode(", t.", $tableFields);
			$em = $this->getDoctrine()->getEntityManager();
			$search_like = "LOWER(t.".
			implode(") LIKE LOWER('%".$search_line."%') OR LOWER(t.", $searchFields).
			") LIKE LOWER('%".$search_line."%')";
			if( count($foivId) > 0 ){
				$query = $em->createQuery(
				'
				SELECT t FROM NCUOFoivBundle:'.$entitiyTableName.' t WHERE t.'.$fieldFoivIdName.' IN (:foivId) AND ('.$search_like.')
				'
				)
				->setParameter('foivId', implode(", ", $foivId) )
				;
			}else{
				$query = $em->createQuery(
				'
				SELECT t FROM NCUOFoivBundle:'.$entitiyTableName.' t WHERE '.$search_like.'
				'
				)
				;
			}
		}
		$results["sql"] = $query->getSql();
		$results["searchResult"] = $query->getResult();
		$results["cntResult"] = count($results["searchResult"]);
			
		return $results;
    }
        
	private function getLower($value){
		$value = mb_strtolower($value, "UTF-8");
		return $value;
	}
    
    

}