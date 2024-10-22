    $(function() {
        $('#dialogExport').dialog({
            buttons: [{text: "Закрыть", click: closeExportDialog }],
            modal: true,
            autoOpen: false,
            width: 340
        })
            
        $('#dialogImport').dialog({
            buttons: [{text: "Загрузить", click: sendImport },{text: "Закрыть", click: closeImportDialog }],
            modal: true,
            autoOpen: false,
            width: 400
        })
            
        function closeExportDialog() { 
            $('#dialogExport').dialog("close");
        }
        function closeImportDialog() { 
            $('#dialogImport').dialog("close");
        }
        function sendImport(){
            $('#frmImport').submit();
        }

        $('#dialogExport').dialog("open");
                
    });

    function openImportDialog(){
                $('#dialogImport').dialog("open");
    } 