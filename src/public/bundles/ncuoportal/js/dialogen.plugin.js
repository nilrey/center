/**
 * Плагин для создания диалогов
 * ############################
 * 
 * Зависимости:
 *  jQuery UI
 */

(function($){

    /**
     * Настройки
     */
    
    var settings = {
        resizable: false,
        width: 300,
        height: 200,
        position: {at: 'center center-200'},
        modal: true,
        title: 'Информация',
        text: ''
    };    
    
    /**
     * Функция проверки наличия div для диалога
     */
    
    function checkDlgDiv() {
        if ($('#dialogen_msg_dlg').length == 0) {
            $('body').append("<div id='dialogen_msg_dlg' title='' style='display: none;'></div>");
        }
    }    
    
    /**
     * Методы плагина
     */
    
    var methods = {
        
        /**
         * Инициализация
         */
        
        init: function(options) {
        },


        /**
         * Диалог сообщения с кнопкой Ок
         * #############################
         * 
         * Вход:
         *  options     - array - расширение настроек settings
         *  callback            - callback-функция при нажатии на Ок
         * 
         */
        
        okdlg: function(options, callback) {
            checkDlgDiv();
            
            var opt = $.extend({}, settings, options);
                       
            $('#dialogen_msg_dlg').attr('title', opt['title']).html(opt['text']);
            $('#dialogen_msg_dlg').dialog({
                resiziable: opt['resizable'],
                width: opt['width'],
                height: opt['height'],
                position: opt['position'],
                modal: opt['modal'],
            buttons: [{text: "Принять", click: function() {

                        $(this).dialog('destroy');
                        if (typeof callback != 'undefined') {
                            callback.call();
                        }
            } } ]
/*                buttons: {
                    'OK': function() {
                        $(this).dialog('destroy');
                        if (typeof callback != 'undefined') {
                            callback.call();
                        }
                    }
                }   */             
            });
        },
        
        
        /**
         * Диалог подтверждения с кнопками Да/Нет
         * ######################################
         * 
         * Вход:
         *  options         - array - расширение настроек settings
         *  yes_callback            - callback-функция при нажатии на кнопку Да
         *  no_callback             - callback-функция при нажатии на кнопку Нет
         */
        
        yesnodlg: function(options, yes_callback, no_callback) {
            checkDlgDiv();
            
            var opt = $.extend({}, settings, options);
                       
            $('#dialogen_msg_dlg').attr('title', opt['title']).html(opt['text']);
            $('#dialogen_msg_dlg').dialog({
                resiziable: opt['resizable'],
                width: opt['width'],
                position: opt['position'],
                modal: opt['modal'],
                buttons: {
                    'Принять': function() {
                        $(this).dialog('destroy');
                        if (typeof yes_callback != 'undefined')
                            yes_callback.call();
                    },
                    'Отменить': function() {
                        $(this).dialog('destroy');
                        if (typeof no_callback != 'undefined')
                            no_callback.call();
                    }
                }                
            });            
        }
    };
    
    
    /**
     * Основная функция вызова методов плагина
     */
    
    $.dialogen = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || ! method) {
            return methods['init'].apply(this, arguments);
        } else {
            $.error('Метод с именем ' +  method + ' не существует для jQuery.dialogen');
        } 
    };    
    
})(jQuery);