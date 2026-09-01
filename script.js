(function () {
  "use strict";

  var hero = document.getElementById("hero");
  var art = document.getElementById("heroArt");
  var panels = Array.prototype.slice.call(art.querySelectorAll(".hero-panel"));
  var tooltip = document.getElementById("heroTooltip");
  var tooltipName = document.getElementById("tooltipName");
  var tooltipDesc = document.getElementById("tooltipDesc");
  var tooltipLink = document.getElementById("tooltipLink");

  var isTouch = window.matchMedia("(hover: none), (pointer: coarse)").matches;
  var activePanel = null;

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

  // ---- description reveal ----
  function showTooltip(panel) {
    tooltipName.textContent = panel.dataset.name;
    tooltipDesc.textContent = panel.dataset.description;
    tooltipLink.href = panel.getAttribute("href");
    tooltipLink.textContent = "";
    tooltipLink.append("Explore " + panel.dataset.name + " ");
    var arrow = document.createElement("span");
    arrow.setAttribute("aria-hidden", "true");
    arrow.textContent = "→";
    tooltipLink.append(arrow);
    tooltip.style.setProperty("--brand", getComputedStyle(panel).getPropertyValue("--brand"));

    var artRect = art.getBoundingClientRect();
    var panelRect = panel.getBoundingClientRect();
    var x = panelRect.left + panelRect.width / 2 - artRect.left;
    var minX = 120;
    var maxX = artRect.width - 120;
    x = Math.max(minX, Math.min(maxX, x));

    tooltip.style.left = x + "px";
    tooltip.style.top = panelRect.top - artRect.top + "px";
    tooltip.classList.add("is-visible");

    if (activePanel && activePanel !== panel) {
      activePanel.classList.remove("is-active");
    }
    activePanel = panel;
    panel.classList.add("is-active");
  }

  function hideTooltip() {
    tooltip.classList.remove("is-visible");
    if (activePanel) {
      activePanel.classList.remove("is-active");
      activePanel = null;
    }
  }

  panels.forEach(function (panel) {
    panel.addEventListener("mouseenter", function () {
      showTooltip(panel);
    });
    panel.addEventListener("focus", function () {
      showTooltip(panel);
    });
    panel.addEventListener("mouseleave", function () {
      if (!isTouch) hideTooltip();
    });
    panel.addEventListener("blur", function () {
      hideTooltip();
    });

    if (isTouch) {
      panel.addEventListener("click", function (event) {
        // a synthetic "mouseenter" fires before "click" on tap and already
        // shows the tooltip, so activePanel can't tell first tap from
        // second — track "tapped" per panel instead.
        if (!panel.classList.contains("is-tapped")) {
          event.preventDefault();
          panels.forEach(function (p) {
            if (p !== panel) p.classList.remove("is-tapped");
          });
          panel.classList.add("is-tapped");
          showTooltip(panel);
        }
        // second tap on an already-tapped panel follows the link normally
      });
    }
  });

  document.addEventListener(
    "click",
    function (event) {
      if (isTouch && activePanel && !art.contains(event.target)) {
        panels.forEach(function (p) {
          p.classList.remove("is-tapped");
        });
        hideTooltip();
      }
    },
    true
  );

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") hideTooltip();
  });
})();
