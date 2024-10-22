/*
 * Обрабочтчик события удаления ФОИВ
 *
 * FOIV_ID: Идентификатор удаляемого ФОИВ
 * URLString: URL-адрес по которому выполняется запрос удаления данных
 * URLRedirect: URL-адрес по которому выполняется перенеаправления на другую веб-страницу после успешного выполнения операции
*/
 function OnDeleteFoivClick(FOIV_ID, URLString, URLRedirect)
{
    deleteItemDialog('del_foiv_dialog',
                                            FOIV_ID,
                                            URLString,
                                            URLRedirect,
                                            'GET',
                                            "Ошибка удаления ФОИВ. Обратитесь к администратору сайта."
                                            );
}

/*
 * Обработчик события сохранения  информации о ФОИВ
 * FormID - идентифкатор формы, в которой происходит отправка данных на сервер для их сохраниения
 * ElementID - идентификатор HTML-элемента (в даннорм случае div'a)
 *  SuccessMessage - текст сообщения в случае успешного сохранения
 *  ErrorMessage -  текст сообщения в случае неудачи
 */
function OnSaveFoivClick(FormID, ElementID, SuccessMessage,ErrorMessage)
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
                                      success: function(result)
                                      {
                                         //все в порядке
                                          if (result.code == 200)
                                          {
                                              showInfoRedirectDialog(ElementID, SuccessMessage, result.url);
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
                                            showInfoDialog(ElementID, Message);
                                          }
                                           
                                      },
                                      error: function(result)
                                      {
                                          showInfoDialog(ElementID, ErrorMessage);
                                      }
                                      });
    
}
