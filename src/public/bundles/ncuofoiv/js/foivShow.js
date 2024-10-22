$(function () {
    $('#dialogHolder_50').hide();

    $('#deleteFoiv_50').click(function () {
        $('#dialogHolder_50').show();
    });

    $('#cancelDeletion_50').click(function () {
        $('#dialogHolder_50').hide();
    });

    $('.dialogBoxHolder').click(function () {
        this.hide();
    });

    $('.dialogCloser').click(function () {
        $('.dialogBoxHolder').hide();
    });
    
    
    var dTable = $('#dTable').DataTable({
        retrieve: true,
        language: {
            url: DATATABLES_RU_JSON            
        }
    });
     
    $("#importForm").dialog({
        autoOpen: false
    });
    
    $("#importBtn").click(function() {
        $("#importForm").dialog( "open" );
        
        return false;
    });
    
});