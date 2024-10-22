$(document).ready(function() {
    
    /*
     * Таблица меню
     */
    
    var tblMenus = $('#tbl_menus').DataTable({
        language: {
            url: DATATABLES_RU_JSON            
        },
        ordering: false,
        searching: false,
        serverSide: true,
        processing: true,
        ajax: {
            url: URL_GET_MENU_LIST,
            method: 'POST'
        },
        columnDefs: [{className: 'text-center', targets: [2, 3, 4, 5]}]
    });
    
    /**
     * Добавление меню
     */
    
    $('#btn_add_menu').click(function() {
        // Открываем диалог
        var dlg_edit = $('#menu_edit_dlg');
        dlg_edit.attr('title', 'Создание');
        
        dlg_edit.find('#menu_name').val('');
        dlg_edit.find('#menu_parent_id').val('');
        dlg_edit.find('#menu_position').val('');
        dlg_edit.find('#menu_icon').val('');
        dlg_edit.find('#menu_url_type').val(0);
        dlg_edit.find('#menu_url').val('');
        dlg_edit.find('._role').prop('checked', false);
            
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
                    objSave['id_menu']                      = -1;
                    objSave['menu_name']                    = dlg_edit.find('#menu_name').val();
                    objSave['menu_parent_id']               = dlg_edit.find('#menu_parent_id').val();
                    objSave['menu_position']                = dlg_edit.find('#menu_position').val();
                    objSave['menu_icon']                    = dlg_edit.find('#menu_icon').val();
                    objSave['menu_url_type']                = dlg_edit.find('#menu_url_type').val();
                    objSave['menu_url']                     = dlg_edit.find('#menu_url').val();
                    objSave['menu_roles']                   = dlg_edit.find('input:checkbox:checked._role').map(function() { return $(this).attr('role_id') }).get();
                    
                    $('#lock_pane').attr('class', 'lock_on');
                    $.ajax({
                        url: URL_MENU_SAVE,
                        method: 'POST',
                        data: objSave,
                        async: false,
                        cache: false,
                        success: function (e) {
                            $('#lock_pane').attr('class', 'lock_off');
                           
                            if (e['err_id'] == 0) {
                                tblMenus.draw(false);
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
     * Редактирование меню
     */
    
    $('#tbl_menus').on('click', '.menu_edit', function() {
        // Идентификатор меню
        var row_index  = tblMenus.row($(this).closest('tr')).index();
        var id_menu = tblMenus.cell(row_index, 0).data();
               
        // Запрашиваем информацию по меню
        $('#lock_pane').attr('class', 'lock_on');
        $.ajax({
            url: URL_GET_MENU,
            method: 'GET',
            async: false,
            cache: false,
            data: {
                id_menu: id_menu
            },
            success: function(e) {
                $('#lock_pane').attr('class', 'lock_off');
                
                if (e['err_id'] != 0) {
                    $.dialogen('okdlg', {text: e['content']});
                    return;
                }
                
                // Открываем диалог
                var dlg_edit = $('#menu_edit_dlg');
                dlg_edit.attr('title', 'Редактирование');

                dlg_edit.find('#menu_name').val(e['content']['menu_name']);
                dlg_edit.find('#menu_parent_id').val(e['content']['menu_parent_id']);
                dlg_edit.find('#menu_position').val(e['content']['menu_position']);
                dlg_edit.find('#menu_icon').val(e['content']['menu_icon']);
                dlg_edit.find('#menu_url_type').val(e['content']['menu_url_type']);
                dlg_edit.find('#menu_url').val(e['content']['menu_url']);                
                
                dlg_edit.find('._role').prop('checked', false);                
                $.each(e['content']['menu_roles'], function(i, val) {
                    dlg_edit.find("._role[role_id='" + val + "']").prop('checked', true);
                });
               
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
                            objSave['id_menu']                      = id_menu;
                            objSave['menu_name']                    = dlg_edit.find('#menu_name').val();
                            objSave['menu_parent_id']               = dlg_edit.find('#menu_parent_id').val();
                            objSave['menu_position']                = dlg_edit.find('#menu_position').val();
                            objSave['menu_icon']                    = dlg_edit.find('#menu_icon').val();
                            objSave['menu_url_type']                = dlg_edit.find('#menu_url_type').val();
                            objSave['menu_url']                     = dlg_edit.find('#menu_url').val();
                            objSave['menu_roles']                   = dlg_edit.find('input:checkbox:checked._role').map(function() { return $(this).attr('role_id') }).get();
                            
                            $('#lock_pane').attr('class', 'lock_on');
                            $.ajax({
                                url: URL_MENU_SAVE,
                                method: 'POST',
                                data: objSave,
                                async: false,
                                cache: false,
                                success: function (e) {
                                    $('#lock_pane').attr('class', 'lock_off');
                                   
                                    if (e['err_id'] == 0) {
                                        tblMenus.draw(false);
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
     * Удаление меню
     */
    
    $('#tbl_menus').on('click', '.menu_del', function() {
        // Идентификатор меню
        var row_index  = tblMenus.row($(this).closest('tr')).index();
        var id_menu = tblMenus.cell(row_index, 0).data();
        
        $.dialogen(
            'yesnodlg',
            {text: 'Вы действительно хотите удалить меню?'},
            function() {
                // Отправляем запрос на удаление
                var objDel = {};
                objDel['id_menu'] = id_menu;
                
                $('#lock_pane').attr('class', 'lock_on');
                $.ajax({
                    url: URL_MENU_DELETE,
                    method: 'POST',
                    data: objDel,
                    async: false,
                    cache: false,
                    success: function (e) {
                        $('#lock_pane').attr('class', 'lock_off');
                        
                        if (e['err_id'] == 0)
                            tblMenus.draw(false);
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
    $('#menu_edit_dlg #menu_parent_id').numeric();
    $('#menu_edit_dlg #menu_position').numeric();
    
});