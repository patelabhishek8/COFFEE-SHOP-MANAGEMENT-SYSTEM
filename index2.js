document.addEventListener("DOMContentLoaded", function () {
  // Search Venue Functionality
  const searchBox = document.getElementById("searchBox");
  const venueCards = document.querySelectorAll(".venue-card");

  searchBox.addEventListener("input", function () {
    let searchTerm = searchBox.value.toLowerCase();

    venueCards.forEach((card) => {
      let venueName = card
        .querySelector(".venue-name")
        .textContent.toLowerCase();
      if (venueName.includes(searchTerm)) {
        card.style.display = "block";
      } else {
        card.style.display = "none";
      }
    });
  });

  // Login/Signup Button Click Event
  const loginButton = document.getElementById("loginBtn");
  loginButton.addEventListener("click", function () {
    alert("Login/Signup feature coming soon!");
  });

  // Hover Effect for Venue Cards
  venueCards.forEach((card) => {
    card.addEventListener("mouseenter", function () {
      card.style.transform = "scale(1.1)";
      card.style.transition = "transform 0.3s ease";
    });

    card.addEventListener("mouseleave", function () {
      card.style.transform = "scale(1)";
    });
  });
});
