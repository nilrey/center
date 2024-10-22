function openFormNewOption(FormID)
{
	//см. файл DialogHelpers.js
	ShowHideFormPanel(FormID, 500);
}

/*
* Обработчик события успешного Ajax-запроса
*
* data: возвращаемые от сервера данные
*/
function OnSuccess(data)
{
	 //$('.results').html(data.content);
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
		if (data.err_id == 2) 
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
 // $('.results').html(jqXHR.responseText );
   if(jqXHR.status == 404)
   {
		showInfoDialog('dialog_msg_info',  "Нужная веб-страница не найдена или URL-адрес неверный");
   }
   else 
   if(jqXHR.status == 500)
   {
	   showInfoDialog('dialog_msg_info',  "Внутренняя ошибка сервера");
   }
   else
   {
		showInfoDialog('dialog_msg_info',  jqXHR.responseText);
   }
}

/*
* Функция создания новой персоны вышестоящего руководителя
* 
* ActionURL: URL-адрес запроса на создание новой персоны вышестоящего руководителя
*/
function createSelectOption(ActionURL)
{
	//подготавливаем данные к отправке (см. файл CreatePersonFormHelpers.js)
	var Data = prepare();
       
	//см. файл SendDataHelpers.js
	SendDataToServer(Data, "POST", ActionURL, OnSuccess, OnError);
}
