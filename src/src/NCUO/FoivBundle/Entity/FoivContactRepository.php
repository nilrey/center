<?php
namespace App\NCUO\FoivBundle\Entity;

use Doctrine\ORM\EntityRepository;


class FoivContactRepository extends EntityRepository
{
	/*
	* Функция проверки данных на дублирование в таблице dict_person
	*
	* Surname    - Фамилия
	* Name 	     - Имя
	* Patronymic - Отчество
	* Position   - Должность
	* Service 	 - Наименование службы, в котрой рабаотет данная персона
	* PersonID	 - Идентификатор персоны(используется в режиме редактирования)
	*
	* Возврат - результат проверки (TRUE - есть дублирующие записи / FALSE - нет дублирующих записей)
	*/
	public function checkDuplicate($Surname, $Name, $Patronymic, $Position, $Service, $PersonID = NULL)
	{
		$SQLText =  "SELECT count(p.id) as num_p FROM eif_data2.dict_person p WHERE p.Surname=:sur and  p.Name=:nm and p.Patronymic = :patr and p.Position = :pos and p.service = :srv";
		
		//если имеется идентификатор той персоны, у которой данные проверяются, 
		//то данная персона исключается из результатов поиска
		if($PersonID !== NULL )
		{
			$SQLText = $SQLText." and not p.id = :pid";
		}

		$em = $this->getEntityManager();
		$connection = $em->getConnection();
		$statement = $connection->prepare($SQLText);
		$statement->bindValue('sur', $Surname);
		$statement->bindValue('nm', $Name);
		$statement->bindValue('patr', $Patronymic);
		$statement->bindValue('pos', $Position);
		$statement->bindValue('srv', $Service); 
		
		if($PersonID !== NULL )
		{
			$statement->bindValue('pid', $PersonID);
		}
		
		$statement->execute();
		$results = $statement->fetchAll();
		
		$DuplicateNumber = (int)$results[0]["num_p"];
		
		if($DuplicateNumber == null || $DuplicateNumber == 0)
		{
			return FALSE;
		}
		
		return TRUE;
	}
	
	public function getDataFromOldFields()
	{
		$SQLText = "SELECT FC.NAME as \"FIO\", FC.ADDRESS as \"ADDR\", FC.EMAIL as \"EMAIL\", FC.PHONE_NUMBERS as \"PHONE\", FC.PERSON_ID as \"PID\", FC.ID as \"ID\" FROM eif_data2.DICT_FOIV_CONTACTS FC ORDER BY FC.ID";
		
		$em = $this->getEntityManager();
		$connection = $em->getConnection();
		$statement = $connection->prepare($SQLText);
		$statement->execute();
		
		$results = $statement->fetchAll();
		
		return $results;
	}
}
?>