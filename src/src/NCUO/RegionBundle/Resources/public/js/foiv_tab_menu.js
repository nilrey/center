var arrTabs = [];
function setTabsActive() {
    if ( arrTabs.length > 0 ) {
        for( i in arrTabs ){  
            $("#"+arrTabs[i]).addClass("active");
        }
    }
}

$(document).ready( function (){
    setTabsActive();
} )
