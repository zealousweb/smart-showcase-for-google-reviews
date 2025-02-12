import {zwssgrProcessBatch} from './process-batches';
import {zwssgrGetUrlParameter} from './get-url-parameter';

document.addEventListener('DOMContentLoaded', function () {
    "use strict";
    const zwssgrAccountSelect = document.querySelector("#fetch-gmb-data #zwssgr-account-select");

    if (zwssgrAccountSelect) {
    
        zwssgrAccountSelect.addEventListener("change", function () {
            "use strict";
            
            const zwssgrAccountNumber = zwssgrAccountSelect.value;
            const zwssgrAccountName   = zwssgrAccountSelect.options[zwssgrAccountSelect.selectedIndex].text;
            
            document.querySelectorAll("#fetch-gmb-data #zwssgr-location-select, .zwssgr-submit-btn, #fetch-gmd-reviews").forEach(zwssgrEl => zwssgrEl.remove());
            
            if (zwssgrAccountNumber) {

                zwssgrAccountSelect.disabled = true;
                
                const zwssgrWidgetId = zwssgrGetUrlParameter("zwssgr_widget_id");
                
                document.querySelector("#fetch-gmb-data .response").innerHTML = '';
                document.querySelector("#fetch-gmb-data .progress-bar").classList.add("active");
    
                try {
                    zwssgrProcessBatch(
                        "zwssgr_gmb_locations",
                        zwssgrAccountNumber,
                        null,
                        zwssgrWidgetId,
                        null,
                        null,
                        zwssgrAccountName
                    );
                } catch (error) {					
                    document.querySelector("#fetch-gmb-data .progress-bar").classList.remove("active");
                    document.querySelector("#fetch-gmb-data .response").innerHTML = "<p class='error'>An error occurred while processing your request.</p>";
                }

            }
        });
    
    }
});