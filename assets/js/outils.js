// NOS OUTILS
// gestion Tabs Outils page A propos

if (document.getElementById("logiciels-btn") !== null) {
	const logicielsBtn = document.getElementById("logiciels-btn");
	const infoBtn = document.getElementById("info-btn");
	const batimentBtn = document.getElementById("batiment-btn");
	const environnementBtn = document.getElementById("environnement-btn");

	const logicielsListing = document.getElementById("outils-logiciels");
	const infoListing = document.getElementById("outils-info");
	const batimentListing = document.getElementById("outils-batiment");
	const environnementListing = document.getElementById("outils-environnement");

	// Function to show a specific outils listing and hide others
	function showOutilsListing(listing, btn) {
	  logicielsListing.classList.remove("show");
	  infoListing.classList.remove("show");
	  batimentListing.classList.remove("show");
	  environnementListing.classList.remove("show");

	  logicielsBtn.classList.remove('clicked')
	  infoBtn.classList.remove('clicked')
	  batimentBtn.classList.remove('clicked')
	  environnementBtn.classList.remove('clicked')

	  listing.classList.add('show');
	  btn.classList.add('clicked')
	}

	// Event listeners for nav button clicks
	logicielsBtn.addEventListener("click", function() {
	  showOutilsListing(logicielsListing, logicielsBtn);
	});

	infoBtn.addEventListener("click", function() {
	  showOutilsListing(infoListing, infoBtn);
	});

	batimentBtn.addEventListener("click", function() {
	  showOutilsListing(batimentListing, batimentBtn);
	});

	environnementBtn.addEventListener("click", function() {
	  showOutilsListing(environnementListing, environnementBtn);
	});

	$(document).ready(showOutilsListing(logicielsListing, logicielsBtn));
}