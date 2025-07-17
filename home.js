const categorySelect = document.getElementById("categoryFilter");
const citySelect = document.getElementById("cityFilter");
const searchInput = document.getElementById("searchInput");
const venueCards = document.querySelectorAll(".venue");

function filterVenues() {
  const selectedCategory = categorySelect.value.toLowerCase();
  const selectedCity = citySelect.value.toLowerCase();
  const searchText = searchInput.value.toLowerCase();

  venueCards.forEach((card) => {
    // Split the comma-separated categories into an array
    const categories = card.dataset.category
      .toLowerCase()
      .split(",")
      .map((cat) => cat.trim());
    const city = card.dataset.city.toLowerCase();
    const textMatch = card.innerText.toLowerCase().includes(searchText);

    // Check if the selected category is in the array of categories (or "all" is selected)
    const matchCategory =
      selectedCategory === "all" || categories.includes(selectedCategory);
    const matchCity = selectedCity === "all" || city === selectedCity;

    if (matchCategory && matchCity && textMatch) {
      card.style.display = "block";
    } else {
      card.style.display = "none";
    }
  });
}

categorySelect.addEventListener("change", filterVenues);
citySelect.addEventListener("change", filterVenues);
searchInput.addEventListener("input", filterVenues);
