<?php
namespace App\NCUO\FoivBundle\Entity;


/*
 *Класс, содержащий метаинформацию об другом классе, отображаемого из Doctrine
*/
class ORMObjectMetadata
{
    private $MetadataClass;
    private $FieldsList;
    
    /*
     *Конструктор
     *
     * class - объект полученный с помощью статической функции Create
    */
    public function __construct($class)
    {
        $FieldsList = null;
        $this->MetadataClass = $class;
    }
    
    /*
     *Создание объекта метаинформации
     *
     *$em - Entity manager (менеджер по управлению с сущностями БД)
     *$ClassName - имя класса, отображаемого из Doctrine
     *
     *Возврат: объект мета информации
    */
    
    public static function Create($em, $ClassName)
    {
        return new ORMObjectMetadata($em->getClassMetadata($ClassName));
    }
    
    public static function CreateAlt()
    {
        return new ORMObjectMetadata(null);
    }
    
    public function Init($em, $SchemaName, $TableName)
    {
               
                $conn   = $em->getConnection();
                 
                $SQLCmdText = "select tc.column_name  ,tc.is_nullable from information_schema.columns tc where tc.table_schema=:SCH and tc.table_name=:TBL";
                $stmt = $conn->prepare($SQLCmdText);
                $stmt->bindValue('SCH', $SchemaName);
                $stmt->bindValue('TBL', $TableName);
                $stmt->execute();
                
                $this->FieldsList = array();
                //foreach($stmt->fetchAll() as $row)
                while($row = $stmt->fetch())
                {
                    if($row != null)
                    {
                        $a_row = array_values($row);
                        $this->FieldsList[$a_row[0]] = $a_row[1];
                    }
                }
                
    }
    /*
     * получение имени отображаемого класса
    */
    public function getClassName()
    {
        return $this->MetadataClass->getReflectionClass( )->getShortName();
    }
    
    /*
     *Проверка поля на принадлежность к  обычному отображаемому типу
     * FieldName - имя проверяемого поля
     *Возврат: результат проверки (true - отображаемое поле является обычным / false - отображаемое поле не является обычным )
     */
    public function IsOdinaryField($FieldName)
    {
        return $this->MetadataClass->hasField($FieldName);
    }
    
    /*
     *Проверка поля на принадлежность к  ассоциативному типу (т.е. хранит ссылку на другое поле другого объекта. Это как внешний ключ в таблице в БД)
     * FieldName - имя проверяемого поля
     *Возврат: результат проверки (true - отображаемое поле является ассоциативным / false - отображаемое поле не является ассоциативным )
     */
    public function IsAssociateField($FieldName)
    {
        return $this->MetadataClass->hasAssociation($FieldName);
    }
    
    /*
     *Проверка поля на обязательное использование (при вводе данных на веб-форме) 
     * FieldName - имя проверяемого поля
     *Возврат: результат проверки (true - отображаемое поле является обязательным / false - отображаемое поле не является обязательным )
     */
    public  function IsFieldMandatory(  $FieldName )
    {
        if(!$this->FieldsList || count($this->FieldsList) == 0)
        {
            throw new \Exception("Не найдена информация об полях сущности");
        }
        
        $Value = $this->FieldsList[$FieldName];
        if(!$Value)
        {
            throw new \Exception("Не найдена информация об поле '".$FieldName."' сущности" ); 
        }
        
        if($Value === "YES")
        {
           return false;
        }
        else if($Value === "NO")
        {
            return true;
        }
        
        throw new \Exception("Неверный формат данных   об поле '".$FieldName."' сущности" ); 
       
    }
}

?>