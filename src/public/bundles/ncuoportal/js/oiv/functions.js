
    var titleOpen = '>>> Развернуть текст';
    var titleClose = '<<< Свернуть текст';
    var defFieldHeight = '44px';

    $(document).ready(function(){
        $('.field_wraper').css('height', defFieldHeight);
        $('.field_btn').each(function(index, key) {
            var data_field = $(this).attr('data-field');
            if( parseInt($('#field_data_'+data_field).css('height') ) > parseInt(defFieldHeight) ){
                $(this).css('display', 'block');
                $(this).html(titleOpen);
            }
        });

    });
    function oivHideShow(elemIdName){
        if( $('#field_wraper_'+elemIdName).css('height') == defFieldHeight ){
            $('#field_wraper_'+elemIdName).css('height',  $('#field_data_'+elemIdName).css('height')   );
            $('#field_btn_'+elemIdName).html(titleClose);
        }else{
            $('#field_wraper_'+elemIdName).css('height', defFieldHeight );
            $('#field_btn_'+elemIdName).html(titleOpen);
        }
    }
    
    function ncuFormTitleAppendRegion(regName){
        $('#div_table_title h3').append("в регионе "+regName);
    }