document.querySelectorAll(".toggle-pw").forEach(function (btn) {
  btn.addEventListener("click", function () {
    const input = btn.parentElement.querySelector("input");
    const isVisible = input.type === "text";

    input.type = isVisible ? "password" : "text";
    btn.textContent = isVisible ? "👁️" : "🙈";
    btn.setAttribute("aria-label", isVisible ? "Show password" : "Hide password");
  });
});