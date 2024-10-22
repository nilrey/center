$(document).ready(function() {

    /*
     * Таблица отчетов
     */
    
    var tblReports = $('#tbl_reports').DataTable({
        language: {
            url: DATATABLES_RU_JSON            
        },
        ordering: true,
        searching: true,
        columnDefs: [{className: 'text-center', targets: [2]}]
    });	
	
	/*
	 * Выделение/снятие всех
	 */
	
	var SelUnsel = function(mode) {
		var row_coll = tblReports.$(".report_sel", {"page": "all"});
		row_coll.each(function(ind, el) {
			$(el).prop('checked', mode);
		});		
	}
	
	$('#btn_sel_all').click(function() {
		SelUnsel(true);
	})
	
	$('#btn_unsel_all').click(function() {
		SelUnsel(false);
	})
		
	/*
	 * Сохранение списка отчетов
	 */
	
	$('#btn_save').click(function() {
		var ar_checked = [];
		
		var row_coll = tblReports.$(".report_sel:checked", {"page": "all"});
		row_coll.each(function(ind, el) {
			ar_checked.push($(el).attr('data-id'));
		});
		
		// Отправляем запрос на сохранение
		$.ajax({
			url: URL_REP_LIST_SAVE,
			method: 'POST',
			data: {
				'id_user': $('#id_user').val(),
				'reports_access_list': ar_checked.join()
			},
			async: false,
			cache: false,
			success: function (e) {
				$('#lock_pane').attr('class', 'lock_off');
			   
				$.dialogen('okdlg', {text: e['content']});                            
				dlg_edit.dialog('destroy');
			},
			error: function (e) {
				$('#lock_pane').attr('class', 'lock_off');

				$.dialogen('okdlg', {text: MSG_SERVICE_ERROR});
			}                                                                                           
		});
	});
});