// Select necessary elements
const stars = document.querySelectorAll(".star-rating label");
const starInputs = document.querySelectorAll(".star-rating input");
const feedbackBox = document.querySelector(
  ".rating-section input[type='text']"
);
const reviewButton = document.querySelector(".rating-section button");

// Initialize selected rating
let selectedRatingIndex = -1;

// Function to highlight stars (Left to Right)
function highlightStars(index) {
  requestAnimationFrame(() => {
    stars.forEach((star, i) => {
      star.style.color = i <= index ? "gold" : "white";
    });
  });
}

// Add event listeners for stars
stars.forEach((star, index) => {
  star.addEventListener("mouseenter", () => highlightStars(index));
  star.addEventListener("mouseleave", () =>
    highlightStars(selectedRatingIndex)
  );

  // Click to select rating
  star.addEventListener("click", () => {
    starInputs[index].checked = true; // Select input
    selectedRatingIndex = index; // Store selected rating index
    highlightStars(index);
  });
});

// Handle Review Submission
reviewButton.addEventListener("click", (event) => {
  let selectedRating = [...starInputs].find((star) => star.checked);
  let feedback = feedbackBox.value.trim();

  if (!selectedRating || feedback === "") {
    event.preventDefault(); // Prevent form submission
    alert("Please provide both a rating and feedback.");
    return;
  }

  let ratingValue = parseInt(selectedRating.id.replace("star", "")); // Extract rating number
  // Allow form submission to proceed; reset will happen on page reload
});
