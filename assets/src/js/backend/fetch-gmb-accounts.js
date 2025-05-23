import { zwssgrProcessBatch } from './process-batches';
import { zwssgrGetUrlParameter } from './get-url-parameter';

document.addEventListener('DOMContentLoaded', function () {
    "use strict";
    
    const zwssgrButton = document.querySelector("#fetch-gmb-data #fetch-gmd-accounts");

    if (zwssgrButton) {
        
        zwssgrButton.addEventListener("click", function (zwssgrEv) {
            "use strict";
            
            zwssgrEv.preventDefault();
            
            const zwssgrGmbDataType = zwssgrButton.getAttribute("data-fetch-type");
            const zwssgrWidgetId 	= zwssgrGetUrlParameter("zwssgr_widget_id");
            
            document.querySelector("#fetch-gmb-data .zwssgr-progress-bar").classList.add("active");
            zwssgrButton.classList.add("disabled");
            zwssgrButton.innerHTML = '<span class="spinner is-active"></span> Fetching...';
            
            try {
                zwssgrProcessBatch(zwssgrGmbDataType, null, null, zwssgrWidgetId, null, null, null, null);
            } catch (zwssgrError) {
                console.error("Error processing batch:", zwssgrError);
            }
    
        });
    
    }

});