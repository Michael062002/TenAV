(function () {
  "use strict";

  var hero = document.getElementById("hero");
  var art = document.getElementById("heroArt");
  var panels = Array.prototype.slice.call(art.querySelectorAll(".hero-panel"));

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

  // ---- touch: first tap expands + reveals the CTA, second tap follows the link ----
  if (isTouch) {
    panels.forEach(function (panel) {
      panel.addEventListener("click", function (event) {
        if (!panel.classList.contains("is-active")) {
          event.preventDefault();
          panels.forEach(function (p) {
            if (p !== panel) p.classList.remove("is-active");
          });
          panel.classList.add("is-active");
        }
        // second tap on an already-active panel follows the link normally
      });
    });

    document.addEventListener(
      "click",
      function (event) {
        if (!art.contains(event.target)) {
          panels.forEach(function (p) {
            p.classList.remove("is-active");
          });
        }
      },
      true
    );

    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape") {
        panels.forEach(function (p) {
          p.classList.remove("is-active");
        });
      }
    });
  }
})();
