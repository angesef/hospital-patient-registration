const form = document.getElementById("signupForm");
const errorBox = document.getElementById("error");

form.addEventListener("submit", function (event) {
  const firstName = document.getElementById("first_name").value.trim();
  const lastName = document.getElementById("last_name").value.trim();
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value;
  const confirmPassword = document.getElementById("confirm_password").value;
  const bloodGroup = document.getElementById("blood_group").value;
  const file = document.getElementById("referral_letter").files[0];

  let message = "";

  if (firstName === "" || lastName === "") {
    message = "Please enter your first and last name.";
  } else if (!/^\S+@\S+\.\S+$/.test(email)) {
    message = "Please enter a valid email address.";
  } else if (password.length < 8) {
    message = "Password must be at least 8 characters.";
  } else if (password !== confirmPassword) {
    message = "Passwords do not match.";
  } else if (bloodGroup === "") {
    message = "Please select your blood group.";
  } else if (!file) {
    message = "Please upload your referral letter.";
  } else {
    const allowedTypes = ["application/pdf", "image/jpeg", "image/png"];
    if (!allowedTypes.includes(file.type)) {
      message = "Referral letter must be a PDF, JPG or PNG.";
    } else if (file.size > 2 * 1024 * 1024) {
      message = "Referral letter must be smaller than 2 MB.";
    }
  }

  if (message !== "") {
    event.preventDefault();
    errorBox.textContent = message;
  }
});

const fileInput = document.getElementById("referral_letter");
const preview = document.getElementById("preview");
let previewUrl = null;

fileInput.addEventListener("change", function () {
  if (previewUrl) {
    URL.revokeObjectURL(previewUrl);
    previewUrl = null;
  }
  preview.innerHTML = "";

  const file = fileInput.files[0];
  if (!file) return;

  if (file.type.startsWith("image/")) {
    previewUrl = URL.createObjectURL(file);
    const img = document.createElement("img");
    img.src = previewUrl;
    img.alt = "Referral letter preview";
    preview.appendChild(img);
  } else {
    const box = document.createElement("div");
    box.className = "file-box";
    box.textContent = "📄 " + file.name;
    preview.appendChild(box);
  }
});