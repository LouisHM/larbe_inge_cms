// gestion Tabs références page accueil
if (document.getElementById("metiers-btn") !== null) {
	// Get references to the nav buttons and miniatures listings
	const metierBtn = document.getElementById("metiers-btn");
	const activiteBtn = document.getElementById("activites-btn");
	const poleBtn = document.getElementById("poles-btn");

	const metiersListing = document.getElementById("miniatures-metiers");
	const activitesListing = document.getElementById("miniatures-activites");
	const polesListing = document.getElementById("miniatures-poles");

	// Function to show a specific miniatures listing and hide others
	function showMiniaturesListing(listing, btn) {
	  metiersListing.classList.remove('show');
	  activitesListing.classList.remove('show');
	  polesListing.classList.remove('show');

	  metierBtn.classList.remove('clicked');
	  activiteBtn.classList.remove('clicked');
	  poleBtn.classList.remove('clicked');

	  listing.classList.add('show');
	  btn.classList.add('clicked');
	}

	// Event listeners for nav button clicks
	metierBtn.addEventListener("click", function() {
	  showMiniaturesListing(metiersListing, metierBtn);
	});

	activiteBtn.addEventListener("click", function() {
	  showMiniaturesListing(activitesListing, activiteBtn);
	});

	poleBtn.addEventListener("click", function() {
	  showMiniaturesListing(polesListing, poleBtn);
	});

	$(document).ready(showMiniaturesListing(metiersListing, metierBtn));
}
