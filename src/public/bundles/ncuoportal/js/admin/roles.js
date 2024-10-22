$(document).ready(function() {
    
    /*
     * Таблица ролей
     */
    
    var tblRoles = $('#tbl_roles').DataTable({
        language: {
            url: DATATABLES_RU_JSON            
        },
        ordering: false,
        searching: false,
        serverSide: true,
        processing: true,
        ajax: {
            url: URL_GET_ROLE_LIST,
            method: 'POST'
        },
        columnDefs: [{className: 'text-center', targets: [1, 2]}]
    });       
});