
/*
* Функция открытия / сокрытия внутренней формы добавления персоны
* FormID: идентификактор HTML-элемента, котрый представляет собой внутреннюю форму
*/
function openFormNewOption(FormID)
{
	//см. файл DialogHelpers.js
	ShowHideFormPanel(FormID, 500);
}

/*
 * Обрабочтчик события удаления записи в таблице контактов
 *
 *PVO_ID: Идентификатор удаляемой записи в таблицы
 *URLString: URL-адрес по которому выполняется запрос удаления данных
*/
 function OnDeletePVOClick(PVO_ID, URLString)
{
    deleteTableItemDialog('del_pvo_dialog',
						  'pvo_' + PVO_ID,
						  'dTable',
						  URLString,
						  'POST',
						  "Ошибка удаления организации. Обратитесь к администратору сайта."
						 );
}




/*
* Обработчик события успешного Ajax-запроса
*
* data: возвращаемые от сервера данные
*/
function OnSuccess(data)
{
	if (data.err_id == 0) 
	{
			showInfoDialog('dialog_msg_info',  data.res_msg);
			$('[name=director]')
				.append($("<option></option>")
				.attr("value", data.content.id)
				.text(data.content.name));
			
			//очистка контролов от данных (см. файл CreatePersonFormHelpers.js)
			clearEmbededForm();
	}
	else
	{
			showInfoDialog('dialog_msg_info',  data.res_msg);
			if (data.err_id == 2) //есть незаполненные  поля
			{
				//пытаемся выделить цветом контрол (см. файл DialogHelpers.js)
				ColorFocus("newOption_" + data.content.id);
				//$("#newOption_"+data.content.id).css("border", "2px solid red");
			}
	}
	
}

/*
* Обработчик события неудачного Ajax-запроса
*
* jqXHR: Ajax-объект запроса
* textStatus: строка со статусом выполнения
* errorThrown: Текст исключения
*/
function OnError(jqXHR, textStatus, errorThrown)
{
	//$('.results').html(jqXHR.responseText );
   if(jqXHR.status == 404)
   {
		showInfoDialog('dialog_msg_info',  "Нужная веб-страница не найдена или URL-адрес неверный");
   }
   else
   {
		showInfoDialog('dialog_msg_info',  jqXHR.responseText);
   }
}

function createSelectOption(ActionURL)
{
	//см. файл CreatePersonFormHelpers.js
	var Data = prepare();
	
	//см. файл SendDataHelpers.js
	SendDataToServer(Data, "POST", ActionURL, OnSuccess, OnError);
}

