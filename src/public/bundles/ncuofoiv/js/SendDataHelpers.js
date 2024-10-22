
/*
* Функция отправки данных серверу на основе Ajax
* Data: 		   данные отправляемые серверу
* MethodType: 	   строка метода запроса (GET / POST)
* ActionURL:	   URL-адрес для выполнения запроса
* callbackSuccess: ссылка на callback-обработчика успешного выполнения запроса
* callbackError:   ссылка на callback-обработчика неудачного выполнения запроса
*/
function SendDataToServer(Data, MethodType, ActionURL, callbackSuccess, callbackError)
{
	
	
	$.ajax({
		type: 	 MethodType,
		url: 	 ActionURL,
		data: 	 Data,
		dataType:"json",
		success: callbackSuccess,
		error: 	 callbackError
		});
}

/*
* Функция отправки данных серверу на основе Ajax с помощью GET
* Data: 		   данные отправляемые серверу
* ActionURL:	   URL-адрес для выполнения запроса
* callbackSuccess: ссылка на callback-обработчика успешного выполнения запроса
* callbackError:   ссылка на callback-обработчика неудачного выполнения запроса
*/
function SendDataAsGetToServer(Data, ActionURL, callbackSuccess, callbackError)
{
	SendDataToServer(Data, 'GET' ,ActionURL, callbackSuccess, callbackError);
}

/*
* Функция отправки данных серверу на основе Ajax с помощью POST
* Data: 		   данные отправляемые серверу
* ActionURL:	   URL-адрес для выполнения запроса
* callbackSuccess: ссылка на callback-обработчика успешного выполнения запроса
* callbackError:   ссылка на callback-обработчика неудачного выполнения запроса
*/
function SendDataAsPostToServer(Data, ActionURL, callbackSuccess, callbackError)
{
	SendDataToServer(Data, 'POST' ,ActionURL, callbackSuccess, callbackError);
}