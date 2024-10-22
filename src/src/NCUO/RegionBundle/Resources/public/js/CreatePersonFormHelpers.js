/*
* Функция подготовки данных к отправке
* Возврат: массив данных для отправки
*/
function prepare()
{
	var data = {};
	
	if($("#newOption_surname").length !== 0)
	{
		data['surname']    = $("#newOption_surname").val();
	}
	
	if($("#newOption_name").length !== 0)
	{
		data['name'] 	   = $("#newOption_name").val();
	}
	
	if($("#newOption_patronimyc").length !== 0)
	{
		data['patronimyc'] = $("#newOption_patronimyc").val();
	}
	
	if($("#newOption_position").length !== 0)
	{
		data['position']   = $("#newOption_position").val();
	}
	
	if($("#newOption_phone").length !== 0)
	{
		data['phone'] 	   = $("#newOption_phone").val();
	}
	
	if($("#newOption_address").length !== 0)
	{
		data['address']    = $("#newOption_address").val();
	}
	
	if($("#newOption_email").length !== 0)
	{
		data['email'] 	   = $("#newOption_email").val();
	}
	
	if($("#newOption_weburl").length !== 0)
	{
		data['weburl'] 	   = $("#newOption_weburl").val();
	}
	
	if($("#newOption_photo").length !== 0)
	{
		data['photo'] 	   = $("#newOption_photo").val();
	}
		
	//временно комментим начальника
	/*if($("#newOption_supervisor").length !== 0)
	{
		data['supervisor'] = $("#newOption_supervisor").val();
	}*/
	
	return data;
}

/*
* Функция очистки встроенной формы от данных и от выделений рамок красным цветом
*/
function clearEmbededForm()
{
		if($("#newOption_surname").length !== 0)
		{
			$("#newOption_surname").val("");
			$("#newOption_surname").removeAttr("style");
		}
		
		if($("#newOption_name").length !== 0)
		{
			$("#newOption_name").val("");
			$("#newOption_name").removeAttr("style");
		}
		
		if($("#newOption_patronimyc").length !== 0)
		{
			$("#newOption_patronimyc").val("");
			$("#newOption_patronimyc").removeAttr("style");
		}
		
		if($("#newOption_position").length !== 0)
		{
			$("#newOption_position").val("");
			$("#newOption_position").removeAttr("style");
		}
		
		if($("#newOption_phone").length !== 0)
		{
			$("#newOption_phone").val("");
			$("#newOption_phone").removeAttr("style");
		}
		
		if($("#newOption_address").length !== 0)
		{
			$("#newOption_address").val("");
			$("#newOption_address").removeAttr("style");
		}
		
		if($("#newOption_email").length !== 0)
		{
			$("#newOption_email").val("");
			$("#newOption_email").removeAttr("style");
		}
		
		if($("#newOption_weburl").length !== 0)
		{
			$("#newOption_weburl").val("");
			$("#newOption_weburl").removeAttr("style");
		}
		
		if($("#newOption_photo").length !== 0)
		{
			$("#newOption_photo").val("");
			$("#newOption_photo").removeAttr("style");
		}
		
		
		
		//Временно комментим вышестоящего начальника
		/*if($("#newOption_supervisor").length !== 0)
		{
			$("#newOption_supervisor").val(0);
			$("#newOption_supervisor").removeAttr("style");
		}*/
		
}
