(function () {
  "use strict";

  // ---- footer year ----
  var yearEl = document.getElementById("year");
  if (yearEl) yearEl.textContent = new Date().getFullYear();

  var hero = document.getElementById("hero");
  if (!hero) return;
  var cards = Array.prototype.slice.call(hero.querySelectorAll(".hero__card"));

  var isTouch = window.matchMedia("(hover: none), (pointer: coarse)").matches;

  // ---- entrance animation ----
  if ("IntersectionObserver" in window) {
    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            hero.classList.add("hero--in-view");
            io.disconnect();
          }
        });
      },
      { threshold: 0.25 }
    );
    io.observe(hero);
  } else {
    hero.classList.add("hero--in-view");
  }

  // ---- touch: first tap reveals the description, second tap follows the link ----
  if (isTouch) {
    cards.forEach(function (card) {
      card.addEventListener("click", function (event) {
        if (!card.classList.contains("hero__card--active")) {
          event.preventDefault();
          cards.forEach(function (c) {
            if (c !== card) c.classList.remove("hero__card--active");
          });
          card.classList.add("hero__card--active");
        }
        // second tap on an already-active card follows the link normally
      });
    });

    document.addEventListener(
      "click",
      function (event) {
        if (!hero.contains(event.target)) {
          cards.forEach(function (c) {
            c.classList.remove("hero__card--active");
          });
        }
      },
      true
    );

    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape") {
        cards.forEach(function (c) {
          c.classList.remove("hero__card--active");
        });
      }
    });
  }
})();
