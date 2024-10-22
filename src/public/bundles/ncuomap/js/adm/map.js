$(document).ready(function() {
    
    /*
     * Функция добавления незначащих нулей
     */
    function addZerro(val, count)
    {
        var res = val.toString();
        while (res.length < count)
            res = "0" + res;
        return res;
    }
    
    /*
     * Текущая дата
     */
    function currentDateStr()
    {
        var now = new Date();
        return addZerro(now.getDate(), 2) + '.' + addZerro(now.getMonth()+1, 2) + '.' + now.getFullYear();
    }


    /*
     * Таблица выгрузок
     */
    var tblMapUnloads = $('#tbl_unloads').DataTable({
        dom: '<"#dt-search"f><t><"#dt-bottom"pl>',
        language: {
            url: DATATABLES_RU_JSON            
        },
        ordering: false,
        searching: false,
        serverSide: true,
        processing: true,
        ajax: {
            url: URL_GET_MAP_UNLOAD_LIST,
            method: 'POST'
        },
        columnDefs: [{className: 'dt-center', targets: [1, 2, 3, 4]}],
        /*rowCallback: function(row, data, index) {
            // Установка стиля для колонки статуса
            if (data[2] == 'Выполняется')
                $('td:eq(2)', row).css('background-color', '#f0ad4e');
            else if (data[2] == 'Остановлен')
                $('td:eq(2)', row).css('background-color', '#777');
            
            $('td:eq(2)', row).css('color', 'white');
        }*/
    });
    //tblSources.column(0).visible(false); // ID
    
    /**
     * Добавление выгрузки
     */
    
    $('#btn_add_map').click(function() {
        // Открываем диалог
        
        var dlg_edit = $('#map_edit_dlg');
        dlg_edit.attr('title', 'Создание выгрузки ГИС');
        
        dlg_edit.find('#map_name').   val('');
        dlg_edit.find('#map_command').val('');
        dlg_edit.find('#map_date').   text(currentDateStr());
        
        $('#map_edit_dlg').dialog({
            closeOnEscape: false,
            resiziable: false,
            width: 700,
            height: 600,
            position: {at: 'center center-200'},
            modal: true, 
            buttons: {
                Выполнить: function() {
                    // Выполнить скрипт
                    objExec = {};
                    objExec.cmd = dlg_edit.find('#map_command').val();

                    $('#lock_pane').attr('class', 'lock_on');
                    $.ajax({
                        url: URL_MAP_EXEC,
                        method: 'POST',
                        data: objExec,
                        async: false,
                        cache: false,
                        success: function (e) {
                            $('#lock_pane').attr('class', 'lock_off');
                            if (e['err_id'] == 0) 
                                tblMapUnloads.draw(false);
                            else
                                $.dialogen('okdlg', {text: e['content']});                            
                        },
                        error: function (e) {
                            $('#lock_pane').attr('class', 'lock_off');
                            $.dialogen('okdlg', {text: MSG_MAP_ERROR});
                        }                                                                                           
                    });
                },
                'Создать сервис': function() {
                    // Создать сервис
                    objExec = {};
                    objExec.cmd = dlg_edit.find('#map_command').val();

                    $('#lock_pane').attr('class', 'lock_on');
                    $.ajax({
                        url: URL_MAP_ADD_SERVICE,
                        method: 'POST',
                        data: objExec,
                        async: false,
                        cache: false,
                        success: function (e) {
                            $('#lock_pane').attr('class', 'lock_off');
                            if (e['err_id'] == 0) 
                                tblMapUnloads.draw(false);
                            else
                                $.dialogen('okdlg', {text: e['content']});                            
                        },
                        error: function (e) {
                            $('#lock_pane').attr('class', 'lock_off');
                            $.dialogen('okdlg', {text: MSG_MAP_ERROR});
                        }                                                                                           
                    });
                },
                Создать: function() {
                    // Сохраняем на сервер
                    objSave = {};
                    objSave['map_id'     ] = -1;
                    objSave['map_name'   ] = dlg_edit.find('#map_name').val();
                    objSave['map_command'] = dlg_edit.find('#map_command').val();

                    $('#lock_pane').attr('class', 'lock_on');
                    $.ajax({
                        url: URL_MAP_SAVE,
                        method: 'POST',
                        data: objSave,
                        async: false,
                        cache: false,
                        success: function (e) {
                            $('#lock_pane').attr('class', 'lock_off');
                           
                            if (e['err_id'] == 0) {
                                tblMapUnloads.draw(false);
                                dlg_edit.dialog('destroy');
                            }
                            else
                                $.dialogen('okdlg', {text: e['content']});                            
                        },
                        error: function (e) {
                            $('#lock_pane').attr('class', 'lock_off');

                            $.dialogen('okdlg', {text: MSG_MAP_ERROR});
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
     * Редактирование выгрузки
     */
    
    $('#tbl_unloads').on('click', '.map_edit', function() {
        // Идентификатор источника
        var row_index  = tblMapUnloads.row($(this).closest('tr')).index();
        var id_unlooad = tblMapUnloads.cell(row_index, 0).data();
                       
        // Запрашиваем информацию по источнику
        $('#lock_pane').attr('class', 'lock_on');
        $.ajax({
            url: URL_GET_MAP_UNLOAD,
            method: 'GET',
            async: false,
            cache: false,
            data: {
                id_unload: id_unlooad
            },
            success: function(e) {
                $('#lock_pane').attr('class', 'lock_off');
                
                var content = e['content'];
                if (e['err_id'] != 0) {
                    $.dialogen('okdlg', {text: content});
                    return;
                }
                
                // Открываем диалог
                var dlg_edit = $('#map_edit_dlg');
                dlg_edit.attr('title', 'Редактирование');

                dlg_edit.find('#map_name').   val(content['map_name']);
                dlg_edit.find('#map_command').val(content['map_command']);
                dlg_edit.find('#map_date').   text(content['map_created']['date']);
                
                $('#map_edit_dlg').dialog({
                    closeOnEscape: false,
                    resiziable: false,
                    width: 700,
                    height: 600,
                    position: {at: 'center center-200'},
                    modal: true, 
                    buttons: {
                        Выполнить: function() {
                            // Выполнить скрипт
                            objExec = {};
                            objExec.cmd = dlg_edit.find('#map_command').val();
        
                            $('#lock_pane').attr('class', 'lock_on');
                            $.ajax({
                                url: URL_MAP_EXEC,
                                method: 'POST',
                                data: objExec,
                                async: false,
                                cache: false,
                                success: function (e) {
                                    $('#lock_pane').attr('class', 'lock_off');
                                   
                                    if (e['err_id'] == 0) {
                                        tblMapUnloads.draw(false);
                                        //dlg_edit.dialog('destroy');
                                    }
                                    else
                                        $.dialogen('okdlg', {text: e['content']});                            
                                },
                                error: function (e) {
                                    $('#lock_pane').attr('class', 'lock_off');
                                    $.dialogen('okdlg', {text: MSG_MAP_ERROR});
                                }                                                                                           
                            });
                        },
                        'Создать сервис': function() {
                            // Создать сервис
                            objExec = {};
                            objExec.cmd = dlg_edit.find('#map_command').val();
        
                            $('#lock_pane').attr('class', 'lock_on');
                            $.ajax({
                                url: URL_MAP_ADD_SERVICE,
                                method: 'POST',
                                data: objExec,
                                async: false,
                                cache: false,
                                success: function (e) {
                                    $('#lock_pane').attr('class', 'lock_off');
                                    if (e['err_id'] == 0) 
                                        tblMapUnloads.draw(false);
                                    else
                                        $.dialogen('okdlg', {text: e['content']});                            
                                },
                                error: function (e) {
                                    $('#lock_pane').attr('class', 'lock_off');
                                    $.dialogen('okdlg', {text: MSG_MAP_ERROR});
                                }                                                                                           
                            });
                        },
                        Сохранить: function() {
                            // Сохраняем на сервер
                            // Сохраняем на сервер
                            objSave = {};
                            objSave['map_id'     ] = id_unlooad;
                            objSave['map_name'   ] = dlg_edit.find('#map_name').val();
                            objSave['map_command'] = dlg_edit.find('#map_command').val();
                            
                            $('#lock_pane').attr('class', 'lock_on');
                            $.ajax({
                                url: URL_MAP_SAVE,
                                method: 'POST',
                                data: objSave,
                                async: false,
                                cache: false,
                                success: function (e) {
                                    $('#lock_pane').attr('class', 'lock_off');
                                   
                                    if (e['err_id'] == 0) {
                                        tblMapUnloads.draw(false);
                                        dlg_edit.dialog('destroy');
                                    }
                                    else
                                        $.dialogen('okdlg', {text: e['content']});                            
                                },
                                error: function (e) {
                                    $('#lock_pane').attr('class', 'lock_off');
        
                                    $.dialogen('okdlg', {text: MSG_MAP_ERROR});
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

                $.dialogen('okdlg', {text: MSG_MAP_ERROR});                 
            }
        });                
    });
    
    /*
     * Удаление источника
     */
    
    $('#tbl_unloads').on('click', '.map_del', function() {
        // Идентификатор источника
        var row_index  = tblMapUnloads.row($(this).closest('tr')).index();
        var map_id = tblMapUnloads.cell(row_index, 0).data();
        
        $.dialogen(
            'yesnodlg',
            {text: 'Вы действительно хотите удалить геовыгрузку?'},
            function() {
                // Отправляем запрос на удаление
                var objDel = {};
                objDel['map_id'] = map_id;
                
                $('#lock_pane').attr('class', 'lock_on');
                $.ajax({
                    url: URL_MAP_DELETE,
                    method: 'POST',
                    data: objDel,
                    async: false,
                    cache: false,
                    success: function (e) {
                        $('#lock_pane').attr('class', 'lock_off');
                        
                        if (e['err_id'] == 0)
                            tblMapUnloads.draw(false);
                        else
                            $.dialogen('okdlg', {text: e['content']});                            
                    },
                    error: function (e) {
                        $('#lock_pane').attr('class', 'lock_off');

                        $.dialogen('okdlg', {text: MSG_MAP_ERROR});
                    }                                                                   
                });                
            }
        );
    });
    
     /*
     * Очистка прежних данных
     */
    
    $('#tbl_unloads').on('click', '.map_clear', function() {
        // Идентификатор источника
        var row_index  = tblMapUnloads.row($(this).closest('tr')).index();
        var map_query = tblMapUnloads.cell(row_index, 2).data();
        
        $.dialogen(
            'yesnodlg',
            {text: 'Вы действительно хотите очистить историю выгруженных данных?'},
            function() {
                // Отправляем запрос на очистку
                var objDel = {};
                objDel['cmd'] = map_query;
                
                $('#lock_pane').attr('class', 'lock_on');
                $.ajax({
                    url: URL_MAP_CLEAR,
                    method: 'POST',
                    data: objDel,
                    async: false,
                    cache: false,
                    success: function (e) {
                        $('#lock_pane').attr('class', 'lock_off');
                        
                        if (e['err_id'] == 0)
                            tblMapUnloads.draw(false);
                        else
                            $.dialogen('okdlg', {text: e['content']});                            
                    },
                    error: function (e) {
                        $('#lock_pane').attr('class', 'lock_off');

                        $.dialogen('okdlg', {text: MSG_MAP_ERROR});
                    }                                                                   
                });                
            }
        );
    });
    
    /**
     * Ручной запуск выгрузки
     */
    
    $('#tbl_unloads').on('click', '.map_manual_start', function() {
        // Идентификатор источника
        var row_index  = tblMapUnloads.row($(this).closest('tr')).index();
        var id_unlooad = tblMapUnloads.cell(row_index, 0).data();
        var command_unlooad = tblMapUnloads.cell(row_index, 2).data();
                       
        $.dialogen(
            'yesnodlg',
            {text: 'Вы действительно хотите переконфигурировать выгрузку?'},
            function() {
                // Выполнить скрипт
                var objExec = {};
                objExec.cmd = command_unlooad;

                $('#lock_pane').attr('class', 'lock_on');
                $.ajax({
                    url: URL_MAP_ADD_SERVICE,
                    method: 'POST',
                    data: objExec,
                    async: false,
                    cache: false,
                    success: function (e) {
                        $('#lock_pane').attr('class', 'lock_off');
                       
                        if (e['err_id'] == 0) {
                            tblMapUnloads.draw(false);
                            dlg_edit.dialog('destroy');
                        }
                        else
                            $.dialogen('okdlg', {text: e['content']});                            
                    },
                    error: function (e) {
                        $('#lock_pane').attr('class', 'lock_off');
                        $.dialogen('okdlg', {text: MSG_MAP_ERROR});
                    }                                                                                           
                });
            }
        );
    });
    
    
    
    /**
     * переконфигурация всех выгрузок
     */
    $('#btn_reconf_map').click(function() {
                       
        $.dialogen(
            'yesnodlg',
            {text: 'Вы действительно хотите переконфигурировать ВСЕ выгрузки!?'},
            function() {
                // Выполнить скрипт
                $('#lock_pane').attr('class', 'lock_on');
                $.ajax({
                    url: URL_MAP_RECREATE_UNLOADS,
                    method: 'POST',
                    async: false,
                    cache: false,
                    success: function (e) {
                        $('#lock_pane').attr('class', 'lock_off');
                       
                        if (e['err_id'] == 0) {
                            tblMapUnloads.draw(false);
                            $.dialogen('okdlg', {text: 'Переконфигурация закончена.'});  
                        }
                        else
                            $.dialogen('okdlg', {text: e['content']});                            
                    },
                    error: function (e) {
                        $('#lock_pane').attr('class', 'lock_off');
                        $.dialogen('okdlg', {text: MSG_MAP_ERROR});
                    }                                                                                           
                });
            }
        );
    });
    
    
    /**
     * Обновить все сервисы геовыгрузки
     */
    $('#btn_srv_map').click(function() {
        $.dialogen(
            'yesnodlg',
            {text: 'Вы действительно хотите обновить ВСЕ сервисы выгрузки геоданных!?'},
            function() {
                // Выполнить скрипт
                $('#lock_pane').attr('class', 'lock_on');
                $.ajax({
                    url: URL_MAP_RECREATE_SERVICES,
                    method: 'POST',
                    async: false,
                    cache: false,
                    success: function (e) {
                        $('#lock_pane').attr('class', 'lock_off');
                       
                        if (e['err_id'] == 0) {
                            tblMapUnloads.draw(false);
                            $.dialogen('okdlg', {text: 'Обновление сервисов выгрузки геоданных закончено.'});  
                        }
                        else
                            $.dialogen('okdlg', {text: e['content']});                            
                    },
                    error: function (e) {
                        $('#lock_pane').attr('class', 'lock_off');
                        $.dialogen('okdlg', {text: MSG_MAP_ERROR});
                    }                                                                                           
                });
            }
        );
    });
    
    
        
    /**
     * Отображение справки по командам
     */
    
    $('#map_manual_btn').click(function() {
        // Открываем справки
        var dlgMan = $('#map_manual');
        dlgMan.attr('title', 'Команды выгрузки ГИС');
        
        $('#map_manual').dialog({
            closeOnEscape: false,
            resiziable: true,
            width: 500,
            height: 400,
            position: {at: 'right'},
            //modal: true, 
            buttons: {
                Закрыть: function() {
                    $(this).dialog('destroy');
                }
            }
        });
    });
});