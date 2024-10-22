/*
 * Обрабочтчик события удаления записи в таблице контактов
 * 
 *ContactID: Идентификатор удаляемой записи в таблшицы
 *URLString: URL-адрес по которому выполняется запрос удаления данных
*/
function OnDeleteContactClick(ContactID, URLString)
{
  deleteTableItemDialog('del_cont_dialog',
                                                    'contact_' + ContactID,
                                                    'dTable',
                                                    URLString,
                                                    'POST',
                                                    "Ошибка удаления контакта. Обратитесь к администратору сайта."
                                                    );
}


/*
 * Обработчик события создания информации об контакте
 *
 *  FormID :   		идентифкатор формы, в которой происходит отправка данных на сервер для их сохраниения
 *  ElementID: 		идентификатор HTML-элемента (в даннорм случае div'a)
 *  SuccessMessage: текст сообщения в случае успешного сохранения
 *  ErrorMessage:   текст сообщения в случае неудачи
 */
function OnSaveContactClick(FormID, ElementID, SuccessMessage,ErrorMessage)
{
                      var Form_ID =  '#' + FormID;//'create_data';
  
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
                                      success: function(result,status)
                                      {
                                         //есть ли ссылка для редиректа?
										 if(result.url != 'undefined')
										 {
											showInfoRedirectDialog(ElementID, SuccessMessage, result.url);
										 }
										 else
										 {
											 showInfoDialog(ElementID, SuccessMessage);
										 }
                                      },
                                      error: function(result,status)
                                      {
											//определяем наличие JSON-данных
											if (result.responseJSON.error_type !== 'undefined')
                                            {
												//ошибка: требование заполнить нужное поле
												if(result.responseJSON.error_type == "field mandatory")
                                                {
													//находим контрол с нужным именем
													var Control = document.getElementsByName(result.responseJSON.control_id);
													if (Control != null && Control.length > 0)
													{
													  //фокусируемся на нем
													  Control[0].focus();
													}
												}
                                            }
											
											var Message = ErrorMessage;
											//есть ли сообщение в JSON-данных?
											if(result.responseJSON.message != 'undefined')
											{
												Message = Message + " " + result.responseJSON.message;
											}
											
											showInfoDialog(ElementID, Message);
                                      }
                                      });
}
