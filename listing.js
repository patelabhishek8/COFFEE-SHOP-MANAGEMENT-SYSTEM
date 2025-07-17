document.addEventListener("DOMContentLoaded", function () {
  const photoUpload = document.getElementById("photoUpload");
  const previewContainer = document.getElementById("previewContainer");

  // Handle image upload and preview
  photoUpload.addEventListener("change", function () {
    previewContainer.innerHTML = ""; // Clear previous previews
    Array.from(this.files).forEach((file) => {
      const reader = new FileReader();
      reader.onload = function (e) {
        const img = document.createElement("img");
        img.src = e.target.result;
        img.classList.add("preview-img");
        previewContainer.appendChild(img);
      };
      reader.readAsDataURL(file);
    });
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const checkboxes = document.querySelectorAll(".form-check-input");
  const selectedVenues = document.getElementById("selectedVenues");

  checkboxes.forEach(checkbox => {
    checkbox.addEventListener("change", function () {
      let selected = Array.from(checkboxes)
        .filter(checkbox => checkbox.checked)
        .map(checkbox => checkbox.value);
      selectedVenues.textContent = selected.join(", ") || "None";
    });
  });
});
