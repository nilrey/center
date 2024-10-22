/*
*
* Функция получения расширения файла из его имени
*
* FileName: Имя файла 
*/
function getFileExt(FileName)
{
    if (FileName != null && FileName.length > 0)
    {
            var Index = FileName.lastIndexOf('.');
            if (Index != -1)
            {
                return FileName.substr(Index + 1, FileName.length - (Index + 1));
            }
    }
    
    return null;
}

/*
*
* Функция перезагрузки списка файлов
*
* MessageBoxID: идентификатор HTML-элемента для отображения сообщений в MessageBox
*/
function ReloadFilesList (MessageBoxID)
{
      
       //получаем адрес скрипта на сервере, куда нужно отправить форму
      var m_action= $('#RELOAD_URL').val();
            
      $.ajax({
                     type: "POST",
                     url: m_action,
                     datatype: "json",//получаем от сервера ответы в формате JSON
                     async: false,
                     cache: false,
                     success: function(result)
                     {
                        //все в порядке
                         if (result.code == 200)
                         {
                             
                            //обновляем списорк персон в комбобоксе
                            FilesCombobox = $("select[name='PHOTO_FILE']");
                            if (FilesCombobox == null || result.data == null )
                            {
                                 return;
                            }
                            //удаляем старые данные из селектора
                            FilesCombobox.children().remove();
                            var Files = result.data;
                              
                            var CurrentFile = null;
                            var FileID = null;
							  
                            //вставляем обновленные данные в селектор
                            FilesCombobox.append("<option value='-1'  selected>Выберите фото персоны...</option>");
                            for(i=0;i < Files.length; i++)
                            {
                                 CurrentFile = Files[i]["title"];
                                 FileID      = Files[i]["id"];
                                 FilesCombobox.append("<option value='" + FileID  +"'>" + CurrentFile +"</option>");
                            }
                             
                             
                         }
                         else//есть ошибка
                         {
                             var Message =  "Ошибка обновления списка файлов персон. " + result.message;
                              showInfoDialog(MessageBoxID, Message);
                           }
                     },
                     error: function(result)
                     {
                         showInfoDialog(MessageBoxID, "Ошибка обновления списка файлов персон");
                     }
                     });
}

/*
*
* Функция получения пути к графическому файлу по его идентификатору
*
* ImageFileID: 	  Идентификатор фотофайла
* GettingFileURL: URL-ссылка для запроса данных о файле
*
*/
function getImagePathByID(ImageFileID, GettingFileURL)
{
    if (ImageFileID == null)
    {
        return null;
    }
    
     var myObject = {"file_id": ImageFileID};
     var Path = null;   
        $.ajax({
                     type: "POST",
                     url: GettingFileURL,
                     datatype: "json",//получаем от сервера ответы в формате JSON
                     data: {JSON_DATA: JSON.stringify(myObject)},
                     async: false,
                     cache: false,
                     success: function(result)
                     {
                        //все в порядке
                         if (result.code == 200)
                         {
                             if (result.data == null )
                             {
                                 return null;
                             }
                             
                              var CurrentFile = result.data;
                              Path = CurrentFile["path"] + CurrentFile["file_name"];
                              
                         }
                         else
                         {
                            Path = null; 
                         }
                     },
                     error: function(result)
                     {
                         Path = null;  
                     }
                     });
        
        return Path;
}

/*
*
* Обработчик события выбора пользователем фотофайла
*
* SelectorID:					  Идентификаор селектора
* ImageFileID:					  Идентификатор фотофайла
* GettingFileURL:				  URL-ссылка для запроса получения информации об файле
* MessageDlgElementID [reserved]: идентификатор HTML-элемента для отображения сообщений в MessageBox
*
*/         
function OnSelFileChanged( SelectorID, ImageFileID, GettingFileURL, MessageDlgElementID)
{
    if (SelectorID == null || ImageFileID == null)
    {
        return;
    }
    
    //получаем идентификатор файла персоны
    var SelectedFileID = $('#'+SelectorID + ' option:selected').val();
    if (SelectedFileID == null || SelectedFileID == -1)
    {
        return;
    }
    
    //получаем путь к фотофайлу
    var ImageURL = getImagePathByID(SelectedFileID, GettingFileURL);
    if(ImageURL == null)
    {
        return;    
    }
    //меняем "картинку"
    $('#'+ImageFileID).attr("src",ImageURL);
    
}

/*
*
* Обработчик нажатия на кнопку загрузки файла на сервер
*
* SelectedFileControlID: Идентификатор контрола выбора файла
* MessageDlgElementID:   Идентификатор HTML-элемента для отображения сообщений в MessageBox
*/
function OnFileUpload(SelectedFileControlID, MessageDlgElementID)
{
        var SelectedFile = document.getElementById(SelectedFileControlID);
        
        if (SelectedFile == null)
        {
           alert("Контрол с идентификатором ID='" + ControlID + "' не найден" );
           return;
        }
     
         var fileExtension = /image.*/;
         var fileTobeRead = SelectedFile.files[0];
         
		 //проверяем файл на принадлежность к графическому типу
         if (fileTobeRead.type.match(fileExtension))
         {
			 //извлекаем информацию о выбранном файле 
             var FileName    = fileTobeRead.name;
             var FileExt     = getFileExt(FileName);
             var Title       = $('#TITLE_FILE').val();
             var Description = $('#DESCRIPTION_FILE').val();
             var MIMEType    = fileTobeRead.type;
             var FileSize    = fileTobeRead.size;
             
             
             var fileReader = new FileReader();
             fileReader.onError = function(e)
             {
				showInfoDialog(MessageDlgElementID, 'Ошибка чтения файла');
             };
             
             
             
             fileReader.onload = function (e)
                                               {
                                                 //конвертируем двоичные данные в формат Base64.
                                                 //А на стороне сервера производится обратная трансформация.
                                                 var output = btoa(fileReader.result);
                                                 var myObject = {"file_name": FileName,
                                                                 "title": Title,
                                                                 "decription": Description,
                                                                 "mime_type" : MIMEType,
                                                                 "size": FileSize,
                                                                 "file_ext" : FileExt,
                                                                 "data":output };
                                               
                                                 
                                                 $.ajax({
                                                            type: 'POST',
                                                            url: '/foiv/persons/add_photo',
                                                            data: {JSON_DATA: JSON.stringify(myObject)},
                                                            dataType: 'json'
                                                        })
                                                 .done( function( data )
                                                        {
                                                                if (data.code == 200)
                                                                {
                                                                     showInfoDialog(MessageDlgElementID, 'Фотофайл успешно добавлен');
                                                                     ReloadFilesList(MessageDlgElementID);
                                                                }
																else
																{
																	 showInfoDialog(MessageDlgElementID, 'Фотофайл не удалось добавить. ' + data.message);
																}
                                                                          
                                                                          
                                                        })
                                                .fail( function( data )
                                                      {
                                                        showInfoDialog(MessageDlgElementID, 'Ошибка добавления фотофайла.' + data);
                                                    });
                                                 
                                               } ;
            //считываем файл как набор байтов                                               
             fileReader.readAsBinaryString(fileTobeRead);
             
         } 
         else
         {
			 showInfoDialog(MessageDlgElementID, "Данный файл не является графическим");
             return;
         }
}

/*
 * Обработчик события создания информации об контакте
 * FormID:  	   идентифкатор формы, в которой происходит отправка данных на сервер для их сохраниения
 * MessageBoxID:   идентификатор HTML-элемента (в даннорм случае div'a)
 * SuccessMessage: текст сообщения в случае успешного сохранения
 * ErrorMessage:   текст сообщения в случае неудачи
 */
function OnSavePersonClick(FormID, MessageBoxID, SuccessMessage,ErrorMessage)
{
                      var Form_ID =  '#' + FormID;
  
                       //берем из формы метод передачи данных
                        var m_method=$(Form_ID).attr('method');
                        //получаем адрес скрипта на сервере, куда нужно отправить форму
                       var m_action=$(Form_ID).attr('action');
                       var m_data=$(Form_ID).serialize();
                       $.ajax({
                                      type: m_method,
                                      url: m_action,
                                      data: m_data,
                                      datatype: "json",//получаем от сервера ответы в формате JSON
                                       async: false,
                                       cache: false,
                                      // processData: false,
                                      success: function(result)
                                      {
                                         //все в порядке
                                          if (result.code == 200)
                                          {
                                              showInfoRedirectDialog(MessageBoxID, SuccessMessage, result.url);
                                          }
                                          else//есть ошибка
                                          {
                                           //если требуется ввести данные в обязательное поле, то фокусируемся на данном поле      
                                            if (result.error_type == "field mandatory")
                                            {
                                              //находим контрол с нужным именем
                                                var Control = document.getElementsByName(result.control_id);
                                                if (Control != null && Control.length > 0)
                                                {
                                                  //фокусируемся на нем
                                                  Control[0].focus();
                                                }
                                            }

                                            var Message = ErrorMessage + " " + result.message;
                                            showInfoDialog(MessageBoxID, Message);
                                          }
                                           
                                      },
                                      error: function(result)
                                      {
                                          showInfoDialog(MessageBoxID, ErrorMessage);
                                      }
                                      });
}

/*
*
* Обработчик удаления выбранной персоны
*
* PersonID:  идентификатор удлаляемой персоны
* URLString: URL-адрес по которому выполняется запрос удаления данных
*/
function OnDeletePersonClick(PersonID, URLString)
{
  deleteTableItemDialog('del_person_dialog',
                                                    'person_' + PersonID,
                                                    'dTable',
                                                    URLString,
                                                    'GET',
                                                    "Ошибка удаления персоны. Обратитесь к администратору сайта."
                                                    );
}

