
// ==============================
// Header Interactions - header.js
// ==============================

document.addEventListener("DOMContentLoaded", function () {
    const header = document.querySelector("nav");
    const scrollToTopBtn = document.createElement("button");
    scrollToTopBtn.innerHTML = "⬆️";
    scrollToTopBtn.className = "scroll-to-top";
    document.body.appendChild(scrollToTopBtn);

    // Sticky + shadow on scroll
    window.addEventListener("scroll", () => {
        if (window.scrollY > 10) {
            header.classList.add("scrolled");
            scrollToTopBtn.classList.add("visible");
        } else {
            header.classList.remove("scrolled");
            scrollToTopBtn.classList.remove("visible");
        }
    });

    // Scroll to top button functionality
    scrollToTopBtn.addEventListener("click", () => {
        window.scrollTo({ top: 0, behavior: "smooth" });
    });

    // Responsive mobile menu toggle (if you plan to implement hamburger menu later)
    const menuToggle = document.querySelector(".menu-toggle");
    const menu = document.querySelector(".nav-menu");

    if (menuToggle && menu) {
        menuToggle.addEventListener("click", () => {
            menu.classList.toggle("active");
        });
    }
});
