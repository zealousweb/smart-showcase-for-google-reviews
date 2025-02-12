document.addEventListener('DOMContentLoaded', function () {
    "use strict";

    document.querySelectorAll("#gmb-review-data #delete-reply").forEach(zwssgrButton => {

		zwssgrButton.addEventListener("click", function (zwssgrEv) {

			"use strict";
			zwssgrEv.preventDefault();
		
			const zwssgrLoader = document.createElement("span");
			zwssgrLoader.className = "loader is-active";
			zwssgrLoader.style.marginLeft = "10px";
		
			const zwssgrButtons = document.querySelectorAll("#gmb-review-data #update-reply, #gmb-review-data #delete-reply");
			const zwssgrUrlParams = new URLSearchParams(window.location.search);
			const zwssgrWpReviewId = zwssgrUrlParams.get("post");
			const zwssgrJsonMessage = document.querySelector("#gmb-review-data #json-response-message");
		
			zwssgrButtons.forEach(btn => btn.classList.add("disabled"));
			document.querySelector("#gmb-review-data textarea[name='zwssgr_reply_comment']").readOnly = true;
			document.querySelector("#gmb-review-data #delete-reply").after(zwssgrLoader);
		
			fetch(zwssgr_admin.ajax_url, {
				method: "POST",
				headers: {
					"Content-Type": "application/x-www-form-urlencoded"
				},
				body: new URLSearchParams({
					action: "zwssgr_delete_review_reply",
					zwssgr_wp_review_id: zwssgrWpReviewId,
					security: zwssgr_admin.zwssgr_delete_review_reply
				})
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					const zwssgrSafeMessage = document.createElement("div");
					zwssgrSafeMessage.textContent = data.data.message;
					zwssgrJsonMessage.innerHTML = `<div class="notice notice-success"><p>${zwssgrSafeMessage.innerHTML}</p></div>`;
					setTimeout(() => location.reload(), 2000);
				}
			})
			.catch(zwssgrError => {
				console.error("AJAX Error:", zwssgrError);
				zwssgrJsonMessage.innerHTML = `<div class="notice notice-error"><p>Error: ${zwssgrError.message}</p></div>`;
			})
			.finally(() => {
				document.querySelectorAll("#gmb-review-data .loader.is-active").forEach(zwssgrLoader => zwssgrLoader.remove());
			});
		
		});
		
	});

});