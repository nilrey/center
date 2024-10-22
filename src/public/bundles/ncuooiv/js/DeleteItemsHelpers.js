/*
 *Функция показа информационного сообщения в виде диалога
 *
 * ElementID : идентификатор HTML-элемнта (обычно эт div), котрый используется как основа диалога
 * MessageText: текст сообщения
 */
function showInfoDialog(ElementID, MessageText)
 {
    $(document).ready(function()
    {
      var DialogElementID = "#" + ElementID;
      $(DialogElementID).dialog({ autoOpen: false,
          closeOnEscape: true,
          modal:true,
          buttons: {OK: function(){ $(this).dialog("close"); return true;}}
        });
      
      $(DialogElementID).text(MessageText);
      $(DialogElementID).dialog("open");
    });
 }
 
 /*
  * Функция удаления записи из таблицы с предварительным подтверждением с помощью диалога
  * 
  * DialogID: Идентификатор HTML-элемнта (обычно эт div), котрый используется как основа диалога
  *ControlID: Идентификатор удаляемой записи в таблшицы
  *TableID: Идентификатор таблицы
  *URLString: URL-адрес по которому выполняется запрос удаления данных
  *Method: POST или GET
  *ErrorMessage: Текст сообщения в случае удаления
   */
function deleteTableItemDialog( DialogID, ControlID,  TableID, URLString, Method, ErrorMessage)
{
                
  $(document).ready(function()
   {
     var DlgID = '#'+DialogID;
     var TblID  = '#' + TableID;
     var TableItemID = "#" + ControlID;
     
     $(DlgID).dialog({ autoOpen: false,
         closeOnEscape: true,
         modal:true,
         buttons: {OK: function()
         {
            $(this).dialog("close");
             $.ajax({
               url: URLString,
               method: Method,
               async: false,
               cache: false,
               success: function (e)
                  {
                    if (e != null && e == '200')
                    {
                          var table = $(TblID ).DataTable();
                          if (table != null)
                          {
                              table.row(TableItemID).remove().draw( false );
                              showInfoDialog('dialog_msg_info', 'Запись успешно удалена.');
                          }
                    }
                    else
                    {
                      showInfoDialog('error_msg_dialog', ErrorMessage);
                      //$("#result").text(e);
                    }
                  },
               error: function (e)
                  {
                       showInfoDialog('error_msg_dialog', ErrorMessage);
                      //$("#result").text(e);
                  }                                                                   
           }); 
         } ,
         Отмена : function()
         {
           $(this).dialog("close");
         }
         
     }
                                             
      });
     
   $(DlgID).dialog("open");
     
   });
}
 
  /*
  * Функция удаления объекта (например из списка)  с предварительным подтверждением с помощью диалога,
  * а после показа осуществляется редирект на другую страницу
  * 
  * DialogID: Идентификатор HTML-элемнта (обычно эт div), котрый используется как основа диалога
  *ControlID: Идентификатор удаляемой записи в таблшицы
  *URLString: URL-адрес по которому выполняется запрос удаления данных
  *URLRedirect: URL-ссылка для редироекта на указанную страницу
  *Method: POST или GET
  *ErrorMessage: Текст сообщения в случае удаления
   */
  function deleteItemDialog( DialogID, ControlID,  URLString, URLRedirect, Method, ErrorMessage)
  {
      $(document).ready(function()
      {
        var DlgID = '#'+DialogID;
       // var TblID  = '#' + TableID;
        //var TableItemID = "#" + ControlID;
        
        $(DlgID).dialog({ autoOpen: false,
            closeOnEscape: true,
            modal:true,
            buttons: {OK: function()
               {
                  $(this).dialog("close");
                   $.ajax({
                     url: URLString,
                     method: Method,
                     async: false,
                     cache: false,
                     success: function (e)
                        {
                          if (e != null && e == '200')
                          {
                              showInfoRedirectDialog('dialog_msg_info', 'Запись успешно удалена.', URLRedirect);
                              
                          }
                          else
                          {
                                 showInfoDialog('error_msg_dialog', ErrorMessage);
                          }
                        },
                     error: function (e)
                        {
                             showInfoDialog('error_msg_dialog', ErrorMessage);
                        }                                                                   
                 }); 
               } ,
            Отмена : function()
               {
                 $(this).dialog("close");
               }
           }
                                                
        });
        $(DlgID).dialog("open");
        
      });
  }