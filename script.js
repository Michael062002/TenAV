(function () {
  "use strict";

  var hero = document.getElementById("hero");

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
})();
