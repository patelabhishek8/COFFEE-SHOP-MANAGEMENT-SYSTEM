let index = 0;
const slides = document.querySelectorAll(".slide");
const totalSlides = slides.length;

function showSlide(i) {
  if (i >= totalSlides) index = 0;
  if (i < 0) index = totalSlides - 1;

  document.querySelector(".slider").style.transform = `translateX(${
    -index * 100
  }%)`;
}

function nextSlide() {
  index++;
  showSlide(index);
}

function prevSlide() {
  index--;
  showSlide(index);
}
