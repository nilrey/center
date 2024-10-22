<?php
namespace App\NCUO\FoivBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use App\NCUO\FoivBundle\Entity\Foiv;
use App\NCUO\FoivBundle\Form\FoivType;

use App\NCUO\FoivBundle\Controller\ErrorResponseGenerator;
/**
 * Controller .
  */
class TableCompareController extends Controller
{
	
	/**
     * Compare source table with dict_person table
     *
     * @Route("/cmp/{SourceTableName}", name="table_comparer")
     * @Method("GET")
     * @Template("ncuofoiv/TableComparer/index.html.twig")
     */
	public function indexAction($SourceTableName)
	{
		
		try
		{
		   //получаем список полей по старой таблицы
		   $OldFieldsNames  = $this->getOldFieldsNames($SourceTableName);
		   //получаем список полей по новой таблицы
		   $NewFieldsNames  = $this->getNewFieldsNames($SourceTableName);
		   //получение данных по старой и новой таблиц
		   $ResultTableData	= $this->getSourceTableData($SourceTableName);
		   
		   //$this->get('logger')->debug("New count items: ".count($NewFieldsNames));   
		   return array('Results'=> $ResultTableData, 
						'SourceTableName' => $SourceTableName,
						'OldFieldsNames' => $OldFieldsNames,
						'OldFieldsNamesCount' => count($OldFieldsNames),
						'NewFieldsNames' => $NewFieldsNames,
						'NewFieldsNamesCount' => count($NewFieldsNames));
		}
		catch(\Exception $ex)
		{
			return new Response($ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR );
		}
	   
    	
	}
	
	/*
	*  Получить список полей из старой таблицы
	*  SourceTableName - имя исходной старой таблицы
	*  Возврат: массив наименований полей
	*/
	private function getOldFieldsNames($SourceTableName)
	{
		if($SourceTableName == null)
		{
			throw new \Exception("Отсутсвует имя исходной таблицы для сравнения");
		}
		
		$OldFieldNames = null;
		if($SourceTableName === 'dict_foiv_contacts')
		{
			//$this->get('logger')->debug("getOldFieldsNames()=> dict_foiv_contacts");
			$OldFieldNames = array();
			$OldFieldNames[0] = "ID";
			$OldFieldNames[1] = "ФИО";
			$OldFieldNames[2] = "Телефоны";
			$OldFieldNames[3] = "Эл. почта";
			$OldFieldNames[4] = "Адрес";
		}
		else
		if($SourceTableName === 'dict_foiv_fdo_persons')
		{
			$OldFieldNames = array();
			$OldFieldNames[0] = "ID";
			$OldFieldNames[1] = "ФИО";
			$OldFieldNames[2] = "Должность";
			$OldFieldNames[3] = "Фото";
			$OldFieldNames[4] = "Ссылка на фото";
			$OldFieldNames[5] = "Телефоны";
			$OldFieldNames[6] = "Веб-сайт";
			$OldFieldNames[7] = "Адрес";
			$OldFieldNames[8] = "Эл. почта";
		}
		else if($SourceTableName === 'dict_sitcenter_person')
		{
			$OldFieldNames = array();
			$OldFieldNames[0] = "ID";
			$OldFieldNames[1] = "ФИО";
			$OldFieldNames[2] = "Должность";
			$OldFieldNames[3] = "Фото";
			$OldFieldNames[4] = "Телефоны";
			$OldFieldNames[5] = "Эл. почта";
			
		}
		else if($SourceTableName === 'dict_foiv_dpt_persons')
		{
			$OldFieldNames = array();
			$OldFieldNames[0] = "ID";
			$OldFieldNames[1] = "ФИО";
			$OldFieldNames[2] = "Должность";
			$OldFieldNames[3] = "Фото";
			$OldFieldNames[4] = "Ссылка на фото";
			$OldFieldNames[5] = "ID фото";
			$OldFieldNames[6] = "Телефоны";
			$OldFieldNames[7] = "Эл. почта";
			$OldFieldNames[8] = "Вебсайт";
			$OldFieldNames[9] = "Адрес";
		}
		else if($SourceTableName === 'dict_foiv_pvo_persons')
		{
			$OldFieldNames = array();
			$OldFieldNames[0] = "ID";
			$OldFieldNames[1] = "ФИО";
			$OldFieldNames[2] = "Должность";
			$OldFieldNames[3] = "Фото";
			$OldFieldNames[4] = "Ссылка на фото";
			$OldFieldNames[5] = "Биография";
			$OldFieldNames[6] = "Телефоны";
			$OldFieldNames[7] = "Эл. почта";
			$OldFieldNames[8] = "Вебсайт";
			$OldFieldNames[9] = "Адрес";
		}
		else if($SourceTableName === 'dict_foiv_persons')
		{
			$OldFieldNames = array();
			$OldFieldNames[0] = "ID";
			$OldFieldNames[1] = "ФИО";
			$OldFieldNames[2] = "Должность";
			$OldFieldNames[3] = "Фото";
			$OldFieldNames[4] = "Ссылка на фото";
			$OldFieldNames[5] = "ID фото";
			$OldFieldNames[6] = "Телефоны";
			$OldFieldNames[7] = "Эл. почта";
			$OldFieldNames[8] = "Вебсайт";
			$OldFieldNames[9] = "Адрес";
		}
			
		
		return $OldFieldNames;
	}
	
	/*
	*  Получить список полей из новой таблицы таблицы (dict_person)
	*  SourceTableName - имя исходной старой таблицы
	*  Возврат: массив наименований полей
	*/
	private function getNewFieldsNames($SourceTableName)
	{
		if($SourceTableName == null)
		{
			throw new \Exception("Отсутсвует имя исходной таблицы для сравнения");
		}
		
		$NewFieldNames = null;
		if($SourceTableName === 'dict_foiv_contacts')
		{
			$NewFieldNames = array();
			
			$NewFieldNames[0] = "Фамилия";
			$NewFieldNames[1] = "Имя";
			$NewFieldNames[2] = "Отчество";
			$NewFieldNames[3] = "Телефоны";
			$NewFieldNames[4] = "Эл. почта";
			$NewFieldNames[5] = "Адрес";
			$NewFieldNames[6] = "Должность";
			$NewFieldNames[7] = "Служба";
		}
		else
		if($SourceTableName === 'dict_foiv_fdo_persons')
		{
			$NewFieldNames = array();
			
			$NewFieldNames[0] = "Фамилия";
			$NewFieldNames[1] = "Имя";
			$NewFieldNames[2] = "Отчество";
			$NewFieldNames[3] = "Должность";
			$NewFieldNames[4] = "Фото";
			$NewFieldNames[5] = "Телефоны";
			$NewFieldNames[6] = "Веб-сайт";
			$NewFieldNames[7] = "Адрес";
			$NewFieldNames[8] = "Эл. почта";
		}
		else if($SourceTableName === 'dict_sitcenter_person')
		{
			$NewFieldNames = array();
			
			$NewFieldNames[0] = "Фамилия";
			$NewFieldNames[1] = "Имя";
			$NewFieldNames[2] = "Отчество";
			$NewFieldNames[3] = "Должность";
			$NewFieldNames[4] = "Фото";
			$NewFieldNames[5] = "Телефоны";
			$NewFieldNames[6] = "Эл. почта";
		}
		else if($SourceTableName === 'dict_foiv_dpt_persons')
		{
			$NewFieldNames = array();
			$NewFieldNames[0] = "Фамилия";
			$NewFieldNames[1] = "Имя";
			$NewFieldNames[2] = "Отчество";
			$NewFieldNames[3] = "Должность";
			$NewFieldNames[4] = "Фото";
			$NewFieldNames[5] = "Телефоны";
			$NewFieldNames[6] = "Эл. почта";
			$NewFieldNames[7] = "Вебсайт";
			$NewFieldNames[8] = "Адрес";
		}
		else if($SourceTableName === 'dict_foiv_pvo_persons')
		{
			$NewFieldNames = array();
			$NewFieldNames[0] = "Фамилия";
			$NewFieldNames[1] = "Имя";
			$NewFieldNames[2] = "Отчество";
			$NewFieldNames[3] = "Должность";
			$NewFieldNames[4] = "Фото";
			$NewFieldNames[5] = "Биография";
			$NewFieldNames[6] = "Телефоны";
			$NewFieldNames[7] = "Эл. почта";
			$NewFieldNames[8] = "Вебсайт";
			$NewFieldNames[9] = "Адрес";
			
		}
		else if($SourceTableName === 'dict_foiv_persons')
		{
			$NewFieldNames = array();
			$NewFieldNames[0] = "Фамилия";
			$NewFieldNames[1] = "Имя";
			$NewFieldNames[2] = "Отчество";
			$NewFieldNames[3] = "Должность";
			$NewFieldNames[4] = "Фото";
			$NewFieldNames[5] = "Телефоны";
			$NewFieldNames[6] = "Эл. почта";
			$NewFieldNames[7] = "Вебсайт";
			$NewFieldNames[8] = "Адрес";
		}
		
		return $NewFieldNames;
	}
	
	/*
	*  Получить наборы данных из старой и новой таблиц
	*  SourceTableName - имя исходной старой таблицы
	*  Возврат: массив данных 
	*/
	private function getSourceTableData($SourceTableName)
	{
		if($SourceTableName == null)
		{
			throw new \Exception("Отсутсвует имя исходной таблицы для сравнения");
		}
		
		
		if($SourceTableName === 'dict_foiv_contacts')
		{
			return getDataFromDictFoivContacts();
		}
		else
		if($SourceTableName === 'dict_foiv_fdo_persons')
		{
			//$this->get('logger')->debug("getSourceTableData() => dict_foiv_fdo_persons");
			return $this->getDataFromDictFoivFDOPersons();
		}
		else if($SourceTableName === 'dict_sitcenter_person')
		{
			return $this->getDataFromDictSitcenterPerson();
		}
		else if($SourceTableName === 'dict_foiv_dpt_persons')
		{
			return $this->getDataFromDictFoivDptPersons();
		}
		else if($SourceTableName === 'dict_foiv_pvo_persons')
		{
			return $this->getDataFromDictFoivPVOPersons();
		}
		else if($SourceTableName === 'dict_foiv_persons')
		{
			return $this->getDataFromDictFoivPersons();
		}
		
		return null;
	}
	
	private function getDataFromDictFoivContacts()
	{
		    //получаем репозирторий для сущности FoivContacts
			$ContactsRepository = $this->getDoctrine()->getRepository("NCUOFoivBundle:FoivContacts");
			if($ContactsRepository == null)
			{
				return ErrorResponseGenerator::getInternalServerError("Не удалось получить репозиторий для FoivContacts");
			}
			
			//получаем данные из старой таблицы (dict_foiv_contacts)
			$TableData = $ContactsRepository->getDataFromOldFields();
			if($TableData == null || count($TableData) == 0)
			{
				return null;
			}
			
			//получаем репозирторий для сущности FoivPerson
			$PersonRepository = $this->getDoctrine()->getManager()->getRepository("NCUOFoivBundle:FoivPerson");
			if($PersonRepository == null)
			{
				return ErrorResponseGenerator::getInternalServerError("Не удалось получить репозиторий для FoivPerson");
			}
			
			$data = array(); 
			//цикл по данным из старой таблицы
			foreach($TableData as $SourceRow)
			{
				$row = array();
				//копируем данные старой таблицы
				$ID = $SourceRow['ID'];
				$row[0] = $ID;
				$row[1] = $SourceRow['FIO'];
				$row[2] = $SourceRow['PHONE'];
				$row[3] = $SourceRow['EMAIL'];
				$row[4] = $SourceRow['ADDR'];
				
				//нужен идентификатор для доступа к сущности FoivPerson
				$PID = $SourceRow['PID'];
				
				//ищем сущность новой таблицы
				$FindedPerson = $PersonRepository->find($PID);
				if($FindedPerson == null)
				{
					return ErrorResponseGenerator::getInternalServerError("Не удалось получить информацию о персоне с идентификатором ".$PID);
				}
				
				//копируем данные новой таблицы
				$row[5] = $FindedPerson->getSurname();
				$row[6] = $FindedPerson->getName();
				$row[7] = $FindedPerson->getPatronymic();
				$row[8] = $FindedPerson->getPhone();
				$row[9] = $FindedPerson->getEmail();
				$row[10] = $FindedPerson->getAddress();
				$row[11] = $FindedPerson->getPosition();
				$row[12] = $FindedPerson->getService();

				array_push($data, $row);
				 
			}
			
			return $data;
	}
	
	private function getDataFromDictFoivFDOPersons()
	{
		$data = null;
		//$this->get('logger')->debug("getDataFromDictFoivFDOPersons()");
		$FDOPersonsRepository = $this->getDoctrine()->getRepository("NCUOFoivBundle:FoivFdoPersons");
		if($FDOPersonsRepository == null)
		{
			return ErrorResponseGenerator::getInternalServerError("Не удалось получить репозиторий для FoivFdoPersons");
		}
		
		$TableData = $FDOPersonsRepository->getDataFromOldFields(); 
		//$this->get('logger')->debug("FDOPersonsRepository->getDataFromOldFields() called!");
		if($TableData == null || count($TableData) == 0)
		{
			return null;
		}
		
		$PersonRepository = $this->getDoctrine()->getManager()->getRepository("NCUOFoivBundle:FoivPerson");
		if($PersonRepository == null)
		{
			return ErrorResponseGenerator::getInternalServerError("Не удалось получить репозиторий для FoivPerson");
		}
		
		$data = array(); 
		foreach($TableData as $SourceRow)
		{
			$row = array();
			
			$ID = $SourceRow['ID'];
			$row[0] = $ID;
			$row[1] = $SourceRow['FIO'];
			$row[2] = $SourceRow['POS'];
			$row[3] = $SourceRow['PHOTO'];
			$row[4] = $SourceRow['PURL'];
			$row[5] = $SourceRow['PHONE'];
			$row[6] = $SourceRow['WURL'];
			$row[7] = $SourceRow['ADDR'];
			$row[8] = $SourceRow['EMAIL'];
			
			
			
			$PID = $SourceRow['PID'];
			
			$FindedPerson = $PersonRepository->find($PID);
			if($FindedPerson == null)
			{
				return ErrorResponseGenerator::getInternalServerError("Не удалось получить информацию о персоне с идентификатором ".$PID);
			}
			
			$row[9] = $FindedPerson->getSurname();
			$row[10] = $FindedPerson->getName();
			$row[11] = $FindedPerson->getPatronymic();
			$row[12] = $FindedPerson->getPosition();
			$row[13] = $FindedPerson->getPhotoFile();
			$row[14] = $FindedPerson->getPhone();
			$row[15] = $FindedPerson->getWebsiteUrl();
			$row[16] = $FindedPerson->getAddress();
			$row[17] = $FindedPerson->getEmail();

			array_push($data, $row);
			 
		}
		
		return $data;
	}
	
	private function getDataFromDictSitcenterPerson()
	{
		$SitPersonRepository = $this->getDoctrine()->getRepository("NCUOFoivBundle:SitcenterPerson");
		if($SitPersonRepository == null)
		{
			return ErrorResponseGenerator::getInternalServerError("Не удалось получить репозиторий для SitcenterPerson");
		}
		
		$TableData = $SitPersonRepository->getDataFromOldFields(); 
		//$this->get('logger')->debug("FDOPersonsRepository->getDataFromOldFields() called!");
		if($TableData == null || count($TableData) == 0)
		{
			return null;
		}
		
		$PersonRepository = $this->getDoctrine()->getManager()->getRepository("NCUOFoivBundle:FoivPerson");
		if($PersonRepository == null)
		{
			return ErrorResponseGenerator::getInternalServerError("Не удалось получить репозиторий для FoivPerson");
		}
		
		$data = array(); 
		foreach($TableData as $SourceRow)
		{
			$row = array();
			$ID = $SourceRow['ID'];
			$row[0] = $ID;
			$row[1] = $SourceRow['FIO'];
			$row[2] = $SourceRow['POS'];
			$row[3] = $SourceRow['PURL'];
			$row[4] = $SourceRow['PHONE'];
			$row[5] = $SourceRow['EMAIL'];
		
			$PID = $SourceRow['PID'];
			
			$FindedPerson = $PersonRepository->find($PID);
			if($FindedPerson == null)
			{
				return ErrorResponseGenerator::getInternalServerError("Не удалось получить информацию о персоне с идентификатором ".$PID);
			}
			
			$row[6] = $FindedPerson->getSurname();
			$row[7] = $FindedPerson->getName();
			$row[8] = $FindedPerson->getPatronymic();
			$row[9] = $FindedPerson->getPosition();
			$row[10] = $FindedPerson->getPhotoFile();
			$row[11] = $FindedPerson->getPhone();
			$row[12] = $FindedPerson->getEmail();

			array_push($data, $row);
		}
		
		return $data;
	}
	
	private function getDataFromDictFoivDptPersons()
	{
		$DptPersonRepository = $this->getDoctrine()->getRepository("NCUOFoivBundle:FoivDptPersons");
		if($DptPersonRepository == null)
		{
			return ErrorResponseGenerator::getInternalServerError("Не удалось получить репозиторий для FoivDptPersons");
		}
	
		$TableData = $DptPersonRepository->getDataFromOldFields(); 
		if($TableData == null || count($TableData) == 0)
		{
			return null;
		}
		
		$PersonRepository = $this->getDoctrine()->getManager()->getRepository("NCUOFoivBundle:FoivPerson");
		if($PersonRepository == null)
		{
			return ErrorResponseGenerator::getInternalServerError("Не удалось получить репозиторий для FoivPerson");
		}
		
		$data = array(); 
		foreach($TableData as $SourceRow)
		{
			$row = array();
			$ID = $SourceRow['ID'];
			$row[0] = $ID;
			$row[1] = $SourceRow['FIO'];
			$row[2] = $SourceRow['POS'];
			$row[3] = $SourceRow['PHOTO'];
			$row[4] = $SourceRow['PURL'];
			$row[5] = $SourceRow['FID'];
			$row[6] = $SourceRow['PHONE'];
			$row[7] = $SourceRow['EMAIL'];
			$row[8] = $SourceRow['WURL'];
			$row[9] = $SourceRow['ADDR'];
		
			$PID = $SourceRow['PID'];
			$FindedPerson = $PersonRepository->find($PID);
			if($FindedPerson == null)
			{
				return ErrorResponseGenerator::getInternalServerError("Не удалось получить информацию о персоне с идентификатором ".$PID);
			}
			
			$row[10] = $FindedPerson->getSurname();
			$row[11] = $FindedPerson->getName();
			$row[12] = $FindedPerson->getPatronymic();
			$row[13] = $FindedPerson->getPosition();
			$row[14] = $FindedPerson->getPhotoFile();
			$row[15] = $FindedPerson->getPhone();
			$row[16] = $FindedPerson->getEmail();
			$row[17] = $FindedPerson->getWebsiteUrl();
			$row[18] = $FindedPerson->getAddress();

			array_push($data, $row);
		}
		
		return $data;
	}
	
	private function getDataFromDictFoivPVOPersons()
	{
		$PVOPersonRepository = $this->getDoctrine()->getRepository("NCUOFoivBundle:FoivPvoPersons");
		if($PVOPersonRepository == null)
		{
			return ErrorResponseGenerator::getInternalServerError("Не удалось получить репозиторий для FoivPvoPersons");
		}
	
		$TableData = $PVOPersonRepository->getDataFromOldFields(); 
		if($TableData == null || count($TableData) == 0)
		{
			return null;
		}
		
		$PersonRepository = $this->getDoctrine()->getManager()->getRepository("NCUOFoivBundle:FoivPerson");
		if($PersonRepository == null)
		{
			return ErrorResponseGenerator::getInternalServerError("Не удалось получить репозиторий для FoivPerson");
		}
		
		$data = array(); 
		foreach($TableData as $SourceRow)
		{
			$row = array();
			$ID = $SourceRow['ID'];
			$row[0] = $ID;
			$row[1] = $SourceRow['FIO'];
			$row[2] = $SourceRow['POS'];
			$row[3] = $SourceRow['PHOTO'];
			$row[4] = $SourceRow['PURL'];
			$row[5] = $SourceRow['BIO'];
			$row[6] = $SourceRow['PHONE'];
			$row[7] = $SourceRow['EMAIL'];
			$row[8] = $SourceRow['WURL'];
			$row[9] = $SourceRow['ADDR'];
		
			$PID = $SourceRow['PID'];
			$FindedPerson = $PersonRepository->find($PID);
			if($FindedPerson == null)
			{
				return ErrorResponseGenerator::getInternalServerError("Не удалось получить информацию о персоне с идентификатором ".$PID);
			}
			
			$row[10] = $FindedPerson->getSurname();
			$row[11] = $FindedPerson->getName();
			$row[12] = $FindedPerson->getPatronymic();
			$row[13] = $FindedPerson->getPosition();
			$row[14] = $FindedPerson->getPhotoFile();
			$row[15] = $FindedPerson->getBiography();
			$row[16] = $FindedPerson->getPhone();
			$row[17] = $FindedPerson->getEmail();
			$row[18] = $FindedPerson->getWebsiteUrl();
			$row[19] = $FindedPerson->getAddress();
			
			array_push($data, $row);
		}
		
		return $data;
	}
	
	private function getDataFromDictFoivPersons()
	{
		$FoivPersonRepository = $this->getDoctrine()->getRepository("NCUOFoivBundle:FoivPersons");
		if($FoivPersonRepository == null)
		{
			return ErrorResponseGenerator::getInternalServerError("Не удалось получить репозиторий для FoivPersons");
		}
	
		$TableData = $FoivPersonRepository->getDataFromOldFields(); 
		if($TableData == null || count($TableData) == 0)
		{
			return null;
		}
		
		$PersonRepository = $this->getDoctrine()->getManager()->getRepository("NCUOFoivBundle:FoivPerson");
		if($PersonRepository == null)
		{
			return ErrorResponseGenerator::getInternalServerError("Не удалось получить репозиторий для FoivPerson");
		}
		
		$data = array(); 
		foreach($TableData as $SourceRow)
		{
			$row = array();
			$ID = $SourceRow['ID'];
			$row[0] = $ID;
			$row[1] = $SourceRow['FIO'];
			$row[2] = $SourceRow['POS'];
			$row[3] = $SourceRow['PHOTO'];
			$row[4] = $SourceRow['PURL'];
			$row[5] = $SourceRow['FID'];
			$row[6] = $SourceRow['PHONE'];
			$row[7] = $SourceRow['EMAIL'];
			$row[8] = $SourceRow['WURL'];
			$row[9] = $SourceRow['ADDR'];
		
			$PID = $SourceRow['PID'];
			$FindedPerson = $PersonRepository->find($PID);
			if($FindedPerson == null)
			{
				return ErrorResponseGenerator::getInternalServerError("Не удалось получить информацию о персоне с идентификатором ".$PID);
			}
			
			$row[10] = $FindedPerson->getSurname();
			$row[11] = $FindedPerson->getName();
			$row[12] = $FindedPerson->getPatronymic();
			$row[13] = $FindedPerson->getPosition();
			$row[14] = $FindedPerson->getPhotoFile();
			$row[15] = $FindedPerson->getPhone();
			$row[16] = $FindedPerson->getEmail();
			$row[17] = $FindedPerson->getWebsiteUrl();
			$row[18] = $FindedPerson->getAddress();

			array_push($data, $row);
		}
		
		return $data;
	}
}

?>