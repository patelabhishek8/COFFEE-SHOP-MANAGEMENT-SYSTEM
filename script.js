// this for first index.html
document.addEventListener("DOMContentLoaded", function () {
  const venues = document.querySelectorAll(".venue");
  const verifiedBtn = document.getElementById("verifiedBtn");
  const sortBy = document.getElementById("sortBy");
  const ratingFilter = document.getElementById("ratingFilter");

  // Verified Filter
  verifiedBtn.addEventListener("click", function () {
    verifiedBtn.classList.toggle("active");
    const showVerified = verifiedBtn.classList.contains("active");

    venues.forEach((venue) => {
      if (showVerified) {
        if (venue.getAttribute("data-verified") === "true") {
          venue.style.display = "flex";
        } else {
          venue.style.display = "none";
        }
      } else {
        venue.style.display = "flex";
      }
    });
  });

  // Sorting by Category
  sortBy.addEventListener("change", function () {
    const selectedCategory = this.value;
    venues.forEach((venue) => {
      const categories = venue.getAttribute("data-category").split(",");
      if (selectedCategory === "all" || categories.includes(selectedCategory)) {
        venue.style.display = "flex";
      } else {
        venue.style.display = "none";
      }
    });
  });

  // Sorting by Rating
  ratingFilter.addEventListener("change", function () {
    const selectedRating = parseInt(this.value);
    venues.forEach((venue) => {
      const venueRating = parseInt(venue.getAttribute("data-rating"));
      if (!selectedRating || venueRating === selectedRating) {
        venue.style.display = "flex";
      } else {
        venue.style.display = "none";
      }
    });
  });
});
