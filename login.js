// When  user clicks "Sign Up",show the sign-up form
function showSignUp() {
  document.getElementById("login").style.display = "none";
  document.getElementById("signup").style.display = "block";
  document.getElementById("forgot-password-form").style.display = "none";
}

// When user clicks login, show the login form
function showLogin() {
  document.getElementById("signup").style.display = "none";
  document.getElementById("login").style.display = "block";
  document.getElementById("forgot-password-form").style.display = "none";
}

// When the user clicks "Forgot your password?", show the forgot form below login
function toggleForgotPassword(event) {
  event.preventDefault(); // Prevent the default link behavior

  const forgotForm = document.getElementById("forgot-password-form");

  // Just toggle the forgot form without hiding login/signup
  forgotForm.style.display =
    forgotForm.style.display === "none" ? "block" : "none";
}

// When the user clicks "Confirm OTP", check if OTP is entered and verify it by calling backend
function confirmOTP() {
  const otpInput = document.getElementById("otp-input");
  const otp = otpInput.value.trim();

  if (!otp) {
    alert("Please enter the OTP.");
    return;
  }

  // Send OTP to server to verify
  fetch("../PHP/otp.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `otp=${encodeURIComponent(otp)}`,
  })
    .then((res) => res.text())
    .then((data) => {
      if (data.includes("Verified")) {
        alert("OTP Verified Successfully!");

        // Show password reset fields if OTP is correct
        document.getElementById("new-password-box").style.display = "block";
        document.getElementById("confirm-password-box").style.display = "block";
        document.getElementById("reset-button-box").style.display = "block";

        // Hide OTP confirm button after success
        document.getElementById("confirm-otp-box").style.display = "none";
      } else {
        alert("Invalid OTP. Please try again.");
      }
    })
    .catch((err) => {
      console.error("Error verifying OTP:", err);
    });
}

// This runs when the page finishes loading
window.onload = function () {
  const forgotForm = document.getElementById("forgot-password-form");

  if (forgotForm) {
    // Intercepts the submit event for "Send OTP" in the forgot password form
    forgotForm.addEventListener("submit", function (e) {
      // If reset password section is visible, allow normal form submission
      const resetVisible =
        document.getElementById("reset-button-box").style.display === "block";
      if (resetVisible) return;

      // Otherwise, prevent normal form submit because we want to send OTP first
      e.preventDefault();

      const emailInput = this.querySelector('input[name="email"]');
      const email = emailInput.value.trim();

      if (!email) {
        alert("Please enter your email.");
        return;
      }

      // Send request to server to send OTP to the user's email
      fetch("../PHP/otp.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `email=${encodeURIComponent(email)}`,
      })
        .then((res) => res.text())
        .then((data) => {
          alert(data); // Show response from server (OTP sent or failed)

          // Show OTP input and "Confirm OTP" button
          document.getElementById("otp-box").style.display = "block";
          document.getElementById("confirm-otp-box").style.display = "block";

          // Lock email input so user can’t change it after sending OTP
          emailInput.disabled = true;

          // Change "Send OTP" button to "Resend OTP"
          this.querySelector('input[type="submit"]').value = "Resend OTP";
        })
        .catch((err) => {
          console.error("Error sending OTP:", err);
        });
    });
  }
};
