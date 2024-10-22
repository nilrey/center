/*
 *Функция показа информационного сообщения в виде диалога
 *
 * ElementID : идентификатор HTML-элемента (обычно это div), который используется как основа диалога
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
                                                            buttons: [{text: "Принять", click: function() {
                                                                                        $(this).dialog("close");
                                                            } } ]  
                                                            // buttons: {OK: function(){ $(this).dialog("close");}}
                                                          });
           
           $(DialogElementID).text(MessageText);
           $(DialogElementID).dialog("open");
    });
 }
 
 /*
 *Функция показа информационного сообщения в виде диалога, а после показа осуществляется редирект
 *
 * ElementID : идентификатор HTML-элемента (обычно это div), который используется как основа диалога
 * MessageText: текст сообщения
 * RedirectURL: URL-ссылка для осуществления редиректа на указанную страницу
 */
 function showInfoRedirectDialog(ElementID, MessageText, RedirectURL)
 {
    $(document).ready(function()
    {
         var DialogElementID = "#" + ElementID;
           $(DialogElementID).dialog({ autoOpen: false,
                                                            closeOnEscape: true,
                                                            modal:true,
                                                            buttons: {OK: function()
                                                                                    {
                                                                                        $(this).dialog("close");
                                                                                        //подстраховываемся
                                                                                        if (!(RedirectURL == undefined || RedirectURL == null || RedirectURL.length === 0) )
                                                                                        {
                                                                                           window.location.replace(RedirectURL);
                                                                                        }
                                                                                     }
                                                                            }                                                                                     
                                                          });
           
           $(DialogElementID).text(MessageText);
           $(DialogElementID).dialog("open");
    });
 }
 
 /*
 *Функция показа информационного сообщения в виде подтверждения, а после подтверждения осуществляется редирект
 *
 * ElementID : идентификатор HTML-элемента (обычно это div), который используется как основа диалога
 * MessageText: текст сообщения
 * RedirectURL: URL-ссылка после одобрения редиректа 
 */
 function showConfirmDialog(ElementID, MessageText, RedirectURL)
 {
    $(document).ready(function()
    {
         var DialogElementID = "#" + ElementID;
           $(DialogElementID).dialog({ autoOpen: false,
                                                            closeOnEscape: true,
                                                            modal:true,

                                                            buttons: [
                                                                {text: "Принять", click: function() {
                                                                                        $(this).dialog("close");
                                                                                        if (!(RedirectURL == undefined || RedirectURL == null || RedirectURL.length === 0) )
                                                                                        {
                                                                                           window.location.replace(RedirectURL);
                                                                                        }
                                                                } },
                                                                {text: "Отменить", click: function() {
                                                                                        $(this).dialog("close");
                                                                } }
                                                             ]                                                                              
                                                          });
           
           $(DialogElementID).text(MessageText);
           $(DialogElementID).dialog("open");
    });
 }
 
 /*
 *Функция отображения/сокрытия панели на форме 
 *
 * FormID : идентификатор HTML-элемента, который надо показать/скрыть
 * Duration: интервал для анимации раскрытия / закрытия в милисекундах
 */
 function ShowHideFormPanel(FormID, Duration)
 {
    var FormPanel = $("#" + FormID );
	
	//Если интервал анимации не определен, 
	//то назначаем 0 (мгновенное раскрытие / закрытие)
	Duration = Duration || 0;
	
	if (FormPanel.is(":visible"))
	{
		 FormPanel.animate({height : "hide"}, Duration);
	}
	else
	{
		 FormPanel.animate({height : "show"}, Duration);
	}
 }
 
function OnShowHideMiniForm(FormPanelID, ButtonID)
{
   
     var FormPanel =  $('#'  + FormPanelID);
     var Button = $('#'  + ButtonID);
     if (FormPanel == null || Button == null )
     {
         return;
     }
     
	 
	 ShowHideFormPanel(FormPanelID, 500);
     /*if (FormPanel.is(":visible"))
     {
         FormPanel.animate({height : "hide"}, 500);
         Button.text("Добавить");
     }
     else
     {
         Button.text(" Скрыть ");
         FormPanel.animate({height : "show"}, 500);
     }*/
}

function ColorFocus(ControlID)
{
	//пытаемся фокус поставить
	var Control = document.getElementById(ControlID);
	if (Control != 'undefined')
	{
		//выделяем цветом рамки контрол
		$("#" + ControlID).css("border", "2px solid red");
		//фокусируемся на нем
		Control.focus();
	}
}