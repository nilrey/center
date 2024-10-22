$(document).ready(function() {
    
    /*
     * Таблица пользователей
     */
    
    var tblUsers = $('#tbl_users').DataTable({
        language: {
            url: DATATABLES_RU_JSON            
        },
        ordering: false,
        searching: true,
        serverSide: true,
        processing: true,
        ajax: {
            url: URL_GET_USER_LIST,
            method: 'POST'
        },
        columnDefs: [{className: 'text-center', targets: [1, 2, 3, 4, 5, 6, 7]}]
    });
    
    /**
     * Добавление пользователя
     */
    
    $('#btn_add_user').click(function() {
        // Открываем диалог
        var dlg_edit = $('#user_edit_dlg');
        dlg_edit.attr('title', 'Создание');
        
        dlg_edit.find('#username').val('');
        dlg_edit.find('#password').val('');
        dlg_edit.find('#lastname').val('');
        dlg_edit.find('#firstname').val('');
        dlg_edit.find('#middlename').val('');
        dlg_edit.find('#email').val('');
        dlg_edit.find('#position').val('');
        dlg_edit.find('#contactphone').val('');
        dlg_edit.find('#role').val(-1);
        dlg_edit.find('#foiv').val(-1).hide();
        dlg_edit.find('#roiv').val(-1).hide();        
        
        dlg_edit.dialog({
            closeOnEscape: true,
            resiziable: false,
            width: 550,
            height: 570,
            position: {at: 'center center-200'},
            modal: true, 
            buttons: {
                Сохранить: function() {
                    // Сохраняем на сервер
                    objSave = {};
                    objSave['id_user']                      = -1;
                    objSave['username']                     = dlg_edit.find('#username').val();
                    objSave['password']                     = dlg_edit.find('#password').val();
                    objSave['lastname']                     = dlg_edit.find('#lastname').val();
                    objSave['firstname']                    = dlg_edit.find('#firstname').val();
                    objSave['middlename']                   = dlg_edit.find('#middlename').val();
                    objSave['email']                        = dlg_edit.find('#email').val();
                    objSave['position']                     = dlg_edit.find('#position').val();
                    objSave['contactphone']                 = dlg_edit.find('#contactphone').val();
                    objSave['role']                         = dlg_edit.find('#role').val();                    
                    objSave['foiv']                         = dlg_edit.find('#foiv').val();
                    objSave['roiv']                         = dlg_edit.find('#roiv').val();                    
                    
                    $('#lock_pane').attr('class', 'lock_on');
                    $.ajax({
                        url: URL_USER_SAVE,
                        method: 'POST',
                        data: objSave,
                        async: false,
                        cache: false,
                        success: function (e) {
                            $('#lock_pane').attr('class', 'lock_off');
                           
                            if (e['err_id'] == 0) {
                                tblUsers.draw(false);
                                dlg_edit.dialog('destroy');
                            }
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

    /**
     * Редактирование пользователя
     */
    
    $('#tbl_users').on('click', '.user_edit', function() {
        // Идентификатор пользователя
        var row_index  = tblUsers.row($(this).closest('tr')).index();
        var id_user = tblUsers.cell(row_index, 0).data();
               
        // Запрашиваем информацию по пользователю
        $('#lock_pane').attr('class', 'lock_on');
        $.ajax({
            url: URL_GET_USER,
            method: 'GET',
            async: false,
            cache: false,
            data: {
                id_user: id_user
            },
            success: function(e) {
                $('#lock_pane').attr('class', 'lock_off');
                
                if (e['err_id'] != 0) {
                    $.dialogen('okdlg', {text: e['content']});
                    return;
                }
                
                // Открываем диалог
                var dlg_edit = $('#user_edit_dlg');
                dlg_edit.attr('title', 'Редактирование');

                dlg_edit.find('#username').val(e['content']['username']);
                dlg_edit.find('#password').val('');
                dlg_edit.find('#lastname').val(e['content']['lastname']);
                dlg_edit.find('#firstname').val(e['content']['firstname']);
                dlg_edit.find('#middlename').val(e['content']['middlename']);
                dlg_edit.find('#email').val(e['content']['email']);
                dlg_edit.find('#position').val(e['content']['position']);
                dlg_edit.find('#contactphone').val(e['content']['contactphone']);
                
                var role = e['content']['role'];
                dlg_edit.find('#role').val(role);                    
                dlg_edit.find('#foiv').val(e['content']['foiv']);
                dlg_edit.find('#roiv').val(e['content']['roiv']);
                
                role = e['content']['role_name'];
                if (role == 'ROLE_FOIV') {
                    dlg_edit.find('#foiv').show();
                    dlg_edit.find('#roiv').show();
                } else {
                    dlg_edit.find('#foiv').hide();
                    dlg_edit.find('#roiv').hide();            
                }
               
                dlg_edit.dialog({
                    closeOnEscape: true,
                    resiziable: false,
                    width: 550,
                    height: 570,
                    position: {at: 'center center-200'},
                    modal: true, 
                    buttons: {
                        Сохранить: function() {
                            // Сохраняем на сервер
                            objSave = {};
                            objSave['id_user']                      = id_user;
                            objSave['username']                     = dlg_edit.find('#username').val();
                            objSave['password']                     = dlg_edit.find('#password').val();
                            objSave['lastname']                     = dlg_edit.find('#lastname').val();
                            objSave['firstname']                    = dlg_edit.find('#firstname').val();
                            objSave['middlename']                   = dlg_edit.find('#middlename').val();
                            objSave['email']                        = dlg_edit.find('#email').val();
                            objSave['position']                     = dlg_edit.find('#position').val();
                            objSave['contactphone']                 = dlg_edit.find('#contactphone').val();
                            objSave['role']                         = dlg_edit.find('#role').val();                    
                            objSave['foiv']                         = dlg_edit.find('#foiv').val();
                            objSave['roiv']                         = dlg_edit.find('#roiv').val();
                            
                            $('#lock_pane').attr('class', 'lock_on');
                            $.ajax({
                                url: URL_USER_SAVE,
                                method: 'POST',
                                data: objSave,
                                async: false,
                                cache: false,
                                success: function (e) {
                                    $('#lock_pane').attr('class', 'lock_off');
                                   
                                    if (e['err_id'] == 0) {
                                        tblUsers.draw(false);
                                        dlg_edit.dialog('destroy');
                                    }
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
            },
            error: function(e) {
                $('#lock_pane').attr('class', 'lock_off');

                $.dialogen('okdlg', {text: MSG_SERVICE_ERROR});                 
            }
        });                
    });
    
    /*
     * Удаление пользователя
     */
    
    $('#tbl_users').on('click', '.user_del', function() {
        // Идентификатор пользователя
        var row_index  = tblUsers.row($(this).closest('tr')).index();
        var id_user = tblUsers.cell(row_index, 0).data();
        
        $.dialogen(
            'yesnodlg',
            {text: 'Вы действительно хотите удалить пользователя?'},
            function() {
                // Отправляем запрос на удаление
                var objDel = {};
                objDel['id_user'] = id_user;
                
                $('#lock_pane').attr('class', 'lock_on');
                $.ajax({
                    url: URL_USER_DELETE,
                    method: 'POST',
                    data: objDel,
                    async: false,
                    cache: false,
                    success: function (e) {
                        $('#lock_pane').attr('class', 'lock_off');
                        
                        if (e['err_id'] == 0)
                            tblUsers.draw(false);
                        else
                            $.dialogen('okdlg', {text: e['content']});                            
                    },
                    error: function (e) {
                        $('#lock_pane').attr('class', 'lock_off');

                        $.dialogen('okdlg', {text: MSG_SERVICE_ERROR});
                    }                                                                   
                });                
            }
        );
    });
    
    
    // Диалог редактирования
    $('#user_edit_dlg #role').change(function() {
        var role = $('#user_edit_dlg #role option:selected').attr('data-name');
        
        if (role == 'ROLE_FOIV') {
            $('#user_edit_dlg #foiv').show();
            $('#user_edit_dlg #roiv').show();
        } else {
            $('#user_edit_dlg #foiv').hide();
            $('#user_edit_dlg #roiv').hide();            
        }
    });
	
    /**
     * Переход к настройке доступа к динамическим отчетам
     */
    
    $('#tbl_users').on('click', '.to_user_reports_access', function() {
        // Идентификатор источника
        var row_index  = tblUsers.row($(this).closest('tr')).index();
        var id_user = tblUsers.cell(row_index, 0).data();
        
        window.location.assign(URL_USER_REPORTS_ACCESS + id_user);
    });	
});