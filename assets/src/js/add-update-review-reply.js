document.addEventListener('DOMContentLoaded', function () {
    "use strict";

    document.querySelectorAll("#gmb-review-data #add-reply, #gmb-review-data #update-reply").forEach(zwssgrButton => {

		zwssgrButton.addEventListener("click", function (zwssgrEv) {

			"use strict";
			zwssgrEv.preventDefault();

			const zwssgrReplyCommentElement = document.querySelector("#gmb-review-data textarea[name='zwssgr_reply_comment']");
			const zwssgrReplyComment 		= zwssgrReplyCommentElement.value.trim();
			const zwssgrJsonMessage 		= document.querySelector("#gmb-review-data #json-response-message");
	
			if (!zwssgrReplyComment) {
				zwssgrJsonMessage.innerHTML = '<div class="notice notice-error"> <p> Please enter a valid reply. </p> </div>';
				return;
			}
	
			if (zwssgrReplyComment.length > 4086) {
				zwssgrJsonMessage.innerHTML = '<div class="notice notice-error"> <p> Reply cannot exceed 4086 characters. </p> </div>';
				return;
			}
	
			const zwssgrLoader 			  = document.createElement("span");
			zwssgrLoader.className  	  = "zwssgr-loader is-active";
			zwssgrLoader.style.marginLeft = "10px";
	
			const zwssgrButtons = document.querySelectorAll("#gmb-review-data #add-reply, #gmb-review-data #update-reply, #gmb-review-data #delete-reply");
			const zwssgrUrlParams = new URLSearchParams(window.location.search);
			const zwssgrWpReviewId = zwssgrUrlParams.get("post");
	
			zwssgrButtons.forEach(btn => btn.classList.add("disabled"));
			zwssgrReplyCommentElement.readOnly = true;
			document.querySelector("#gmb-review-data #add-reply, #gmb-review-data #delete-reply").after(zwssgrLoader.cloneNode(true));
	
			fetch(zwssgr_admin.ajax_url, {
				method: "POST",
				headers: {
					"Content-Type": "application/x-www-form-urlencoded"
				},
				body: new URLSearchParams({
					action: "zwssgr_add_update_review_reply",
					zwssgr_wp_review_id: zwssgrWpReviewId,
					zwssgr_reply_comment: zwssgrReplyComment,
					security: zwssgr_admin.zwssgr_add_update_reply_nonce
				})
			})
			.then(zwssgrResponse => zwssgrResponse.json())
			.then(data => {
				if (data.success) {
					const zwssgrSafeMessage = document.createElement("div");
					zwssgrSafeMessage.textContent = data.data.message;
					zwssgrJsonMessage.innerHTML = `<div class="notice notice-success"><p>${zwssgrSafeMessage.innerHTML}</p></div>`;
					setTimeout(() => location.reload(), 2000);
				}
			})
			.catch(zwssgrError => {
				zwssgrJsonMessage.innerHTML = `<div class="notice notice-error"><p>Error: ${zwssgrError.message}</p></div>`;
			})
			.finally(() => {
				document.querySelectorAll("#gmb-review-data .zwssgr-loader.is-active").forEach(zwssgrLoader => zwssgrLoader.remove());
			});
		});

	});

});