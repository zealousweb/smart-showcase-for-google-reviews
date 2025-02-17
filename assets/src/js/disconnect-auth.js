document.addEventListener('DOMContentLoaded', function () {
    "use strict";

    const zwssgrDisconnectButton = document.querySelector("#disconnect-gmb-auth #disconnect-auth");

	if (zwssgrDisconnectButton) {

		zwssgrDisconnectButton.addEventListener("click", function (zwssgrEv) {
			zwssgrEv.preventDefault();
	
			zwssgrDisconnectButton.disabled = true;
			zwssgrDisconnectButton.textContent = "Disconnecting...";
	
			const zwssgrDeletePluginData   = document.querySelector("#disconnect-gmb-auth #delete-all-data").checked ? '1' : '0';
			const zwssgrDeleteDataResponse = document.getElementById("disconnect-gmb-auth-response");
	
			fetch(zwssgr_admin.ajax_url, {
				method: "POST",
				headers: { "Content-Type": "application/x-www-form-urlencoded" },
				body: new URLSearchParams({
					action: "zwssgr_delete_oauth_connection",
					zwssgr_delete_plugin_data: zwssgrDeletePluginData,
					security: zwssgr_admin.zwssgr_delete_oauth_connection,
				})
			})
			.then(response => response.json())
			.then(zwssgrData => {
				if (zwssgrData.success) {
					zwssgrDisconnectButton.disabled = false;
					zwssgrDisconnectButton.textContent = "Disconnected";
					zwssgrDeleteDataResponse.innerHTML = "<p class='success response'> OAuth Disconnected: " + (zwssgrData.data?.message || "Unknown error") + "</p>";
					document.querySelector("#disconnect-gmb-auth .zwssgr-th-label").innerHTML = "";
					document.querySelector("#disconnect-gmb-auth .zwssgr-caution-div").style.display = "none";
					document.querySelector("#disconnect-gmb-auth .danger-note").style.display = "none";
	
					setTimeout(function() {
						window.location.href = zwssgr_admin.zwssgr_redirect;
					}, 1500);
					
				} else {
					zwssgrDeleteDataResponse.innerHTML = "<p class='error response'>Error disconnecting OAuth connection: " + (zwssgrData.data?.message || "Unknown error") + "</p>";
				}
			})
			.catch(zwssgrError => {
				zwssgrDisconnectButton.disabled = false;
				zwssgrDisconnectButton.textContent = "Disconnect";
				zwssgrDeleteDataResponse.innerHTML = "<p class='error response'> An unexpected error occurred: " + zwssgrError + "</p>";
			});
		});
		
	}

});