$(document).ready(function() {
    
    /**
     * Смена пароля
     */
    
    $('#btn_change_pwd').click(function() {
        // Открываем диалог
        var dlg_edit = $('#passwd_dlg');
        
        dlg_edit.find('#passwd').val('');
        dlg_edit.find('#passwd2').val('');                
        
        dlg_edit.dialog({
            closeOnEscape: true,
            resiziable: false,
            width: 350,
            height: 300,
            position: {at: 'center center-200'},
            modal: true, 
            buttons: {
                Сохранить: function() {
                    var pwd = dlg_edit.find('#passwd').val();
                    var pwd2 = dlg_edit.find('#passwd2').val();
                    
                    if (pwd != pwd2) {
                        $.dialogen('okdlg', {text: 'Введенные пароли не совпадают!'});
                        return;
                    }
                    
                    // Сохраняем на сервер
                    objSave = {};
                    objSave['password'] = pwd;
                    
                    $('#lock_pane').attr('class', 'lock_on');
                    $.ajax({
                        url: URL_SAVE_PASSWORD,
                        method: 'POST',
                        data: objSave,
                        async: false,
                        cache: false,
                        success: function (e) {
                            $('#lock_pane').attr('class', 'lock_off');
                           
                            if (e['err_id'] == 0)
                                dlg_edit.dialog('destroy');
                            else
                                $.dialogen('okdlg', {text: e['content']});
                                
                        },
                        error: function (e) {
                            $('#lock_pane').attr('class', 'lock_off');

                            $.dialogen('okdlg', {text: MSG_SERVICE_ERROR});
                        }                                                                                           
                    });
                },
                Отмена: function() {
                    $(this).dialog('destroy');
                }
            }
        });
    });    
});