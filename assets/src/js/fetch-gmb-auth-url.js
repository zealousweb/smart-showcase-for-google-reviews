document.addEventListener('DOMContentLoaded', function () {
    "use strict";

    const zwssgrAuthButton = document.getElementById("fetch-gmb-auth-url");

    if (zwssgrAuthButton) {

		zwssgrAuthButton.addEventListener("click", function (zwssgrEv) {
			"use strict";
			
			zwssgrEv.preventDefault();
			
			const zwssgrAuthResponse     = document.getElementById("fetch-gmb-auth-url-response");
			zwssgrAuthButton.disabled    = true;
			zwssgrAuthButton.textContent = "Connecting...";
			
			fetch(zwssgr_admin.ajax_url, {
				method: "POST",
				headers: {
					"Content-Type": "application/x-www-form-urlencoded"
				},
				body: new URLSearchParams(
					{ 
						action: "zwssgr_fetch_oauth_url" 
					}
				)
			})
			.then(zwssgrResponse => zwssgrResponse.json())
			.then(zwssgrData => {
				if (zwssgrData.success) {
					zwssgrAuthButton.disabled = false;
					zwssgrAuthButton.textContent = "Redirecting...";
					window.location.href = zwssgrData.data.zwssgr_oauth_url;
				} else {
					const zwssgrErrorMessage = document.createElement("p");
					zwssgrErrorMessage.classList.add("error", "response");
					zwssgrErrorMessage.textContent = "Error generating OAuth URL: " + (zwssgrData.data?.message || "Unknown error");
					zwssgrAuthResponse.innerHTML = "";
					zwssgrAuthResponse.appendChild(zwssgrErrorMessage);
				}
			})
			.catch(zwssgrError => {
				zwssgrAuthButton.disabled         = false;
				zwssgrAuthButton.textContent      = "Connect with Google";
				const zwssgrUnexpectedError       = document.createElement("p");
				zwssgrUnexpectedError.classList.add("error", "response");
				zwssgrUnexpectedError.textContent = "An unexpected error occurred: " + zwssgrError;
				zwssgrAuthResponse.innerHTML      = "";
				zwssgrAuthResponse.appendChild(zwssgrUnexpectedError);
			});
			
		});

	}

});