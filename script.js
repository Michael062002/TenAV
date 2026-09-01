(function () {
  "use strict";

  var hero = document.getElementById("hero");
  var art = document.getElementById("heroArt");
  var nodes = Array.prototype.slice.call(art.querySelectorAll(".hero-node"));
  var tooltip = document.getElementById("heroTooltip");
  var tooltipName = document.getElementById("tooltipName");
  var tooltipDesc = document.getElementById("tooltipDesc");
  var tooltipLink = document.getElementById("tooltipLink");

  var isTouch = window.matchMedia("(hover: none), (pointer: coarse)").matches;
  var activeNode = null;

  // clip-path shapes the visible panel, but getBoundingClientRect() on a
  // clipped element still reports the full untransformed box — so the
  // tooltip anchor uses each panel's known visual centre instead.
  var CENTROID_X_PCT = {
    install: 44.278,
    signage: 68.643,
    unify: 90.786,
  };

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
  function showTooltip(node) {
    tooltipName.textContent = node.dataset.name;
    tooltipDesc.textContent = node.dataset.description;
    tooltipLink.href = node.getAttribute("href");
    tooltipLink.textContent = "";
    tooltipLink.append("Explore " + node.dataset.name + " ");
    var arrow = document.createElement("span");
    arrow.setAttribute("aria-hidden", "true");
    arrow.textContent = "→";
    tooltipLink.append(arrow);
    tooltip.style.setProperty("--brand", getComputedStyle(node).getPropertyValue("--brand"));

    var artRect = art.getBoundingClientRect();
    var centroidPct = CENTROID_X_PCT[node.dataset.id] || 50;
    var x = artRect.width * (centroidPct / 100);
    var minX = 120;
    var maxX = artRect.width - 120;
    x = Math.max(minX, Math.min(maxX, x));
    var y = 0;

    tooltip.style.left = x + "px";
    tooltip.style.top = y + "px";
    tooltip.classList.add("is-visible");

    if (activeNode && activeNode !== node) {
      activeNode.classList.remove("is-active");
    }
    activeNode = node;
    node.classList.add("is-active");
  }

  function hideTooltip() {
    tooltip.classList.remove("is-visible");
    if (activeNode) {
      activeNode.classList.remove("is-active");
      activeNode = null;
    }
  }

  nodes.forEach(function (node) {
    node.addEventListener("mouseenter", function () {
      showTooltip(node);
    });
    node.addEventListener("focus", function () {
      showTooltip(node);
    });
    node.addEventListener("mouseleave", function () {
      if (!isTouch) hideTooltip();
    });
    node.addEventListener("blur", function () {
      hideTooltip();
    });

    if (isTouch) {
      node.addEventListener("click", function (event) {
        // a synthetic "mouseenter" fires before "click" on tap and already
        // shows the tooltip, so activeNode can't tell first tap from second —
        // track "tapped" per node instead.
        if (!node.classList.contains("is-tapped")) {
          event.preventDefault();
          nodes.forEach(function (n) {
            if (n !== node) n.classList.remove("is-tapped");
          });
          node.classList.add("is-tapped");
          showTooltip(node);
        }
        // second tap on an already-tapped node follows the link normally
      });
    }
  });

  document.addEventListener(
    "click",
    function (event) {
      if (isTouch && activeNode && !art.contains(event.target)) {
        nodes.forEach(function (n) {
          n.classList.remove("is-tapped");
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
