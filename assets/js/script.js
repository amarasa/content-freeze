document.addEventListener("DOMContentLoaded", function () {
	// Get the modal
	var modal = document.getElementById("cfaModal");

	// Get both close buttons
	var closeButton = document.getElementById("cfaClose");
	var closeButtonFooter = document.getElementById("cfaCloseBtn");

	// Get the checkbox
	var hideCheckbox = document.getElementById("cfaHideCheckbox");

	// Display the modal
	modal.style.display = "block";

	// Function to handle closing the modal
	function closeModal() {
		modal.style.display = "none";

		// If the checkbox is checked, set the cookie to hide the modal for 24 hours
		if (hideCheckbox.checked) {
			setHideCookie();
		}
	}

	// When the user clicks on the close buttons, close the modal
	closeButton.onclick = closeModal;
	closeButtonFooter.onclick = closeModal;

	// Function to set the cookie via AJAX
	function setHideCookie() {
		var xhr = new XMLHttpRequest();
		xhr.open("POST", cfa_params.ajax_url, true);
		xhr.setRequestHeader(
			"Content-Type",
			"application/x-www-form-urlencoded;"
		);
		xhr.onload = function () {
			if (this.status >= 200 && this.status < 400) {
				// Success!
			} else {
				// Error from server
				console.error("Server responded with an error.");
			}
		};
		xhr.onerror = function () {
			// Connection error
			console.error("Could not connect to the server.");
		};
		xhr.send("action=cfa_set_cookie");
	}
});
