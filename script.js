(function () {
  "use strict";

  var hero = document.getElementById("hero");
  var cards = Array.prototype.slice.call(hero.querySelectorAll(".hero-card"));

  var isTouch = window.matchMedia("(hover: none), (pointer: coarse)").matches;

  // ---- entrance animation ----
  if ("IntersectionObserver" in window) {
    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            hero.classList.add("in-view");
            io.disconnect();
          }
        });
      },
      { threshold: 0.25 }
    );
    io.observe(hero);
  } else {
    hero.classList.add("in-view");
  }

  // ---- touch: first tap reveals the description, second tap follows the link ----
  if (isTouch) {
    cards.forEach(function (card) {
      card.addEventListener("click", function (event) {
        if (!card.classList.contains("is-active")) {
          event.preventDefault();
          cards.forEach(function (c) {
            if (c !== card) c.classList.remove("is-active");
          });
          card.classList.add("is-active");
        }
        // second tap on an already-active card follows the link normally
      });
    });

    document.addEventListener(
      "click",
      function (event) {
        if (!hero.contains(event.target)) {
          cards.forEach(function (c) {
            c.classList.remove("is-active");
          });
        }
      },
      true
    );

    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape") {
        cards.forEach(function (c) {
          c.classList.remove("is-active");
        });
      }
    });
  }
})();
