/*
  Enqueue this file via functions-snippet.php (wp_enqueue_script). Do not
  paste it back into an inline <script> tag in the Elementor widget —
  that's the exact pattern that was getting mangled by the live site's
  optimisation layer. As a real enqueued asset it's just a normal .js
  file, so it's excludable via your caching plugin's standard
  file/handle-based exclusion list if needed, rather than relying on
  best-guess data-* opt-out attributes on an inline tag.
*/
(function () {
  try {
    function ready(fn) {
      if (document.readyState !== "loading") fn();
      else document.addEventListener("DOMContentLoaded", fn);
    }

    ready(function () {
      try {
        var lineGen     = 0;
        var liveEntries = [];
        var trackFrame  = null;
        var resizeTimer;

        var hero = document.getElementById("tnav-hero");
        var block = document.getElementById("tnav-block");
        var diagram = document.getElementById("tnav-diagram");
        var centerWrap = document.getElementById("tnav-centerWrap");
        var linesSvg = document.getElementById("tnav-linesSvg");
        var phoneWrap = document.getElementById("tnav-phoneWrap");
        var cardInner = document.getElementById("tnav-cardInner");
        var flipHint = document.getElementById("tnav-flipHint");

        // If any core node is missing (widget duplicated/renamed, markup
        // partially stripped, etc.) bail out instead of throwing.
        if (!hero || !block || !diagram || !centerWrap || !linesSvg) return;

        function computeDs() {
          var dR = diagram.getBoundingClientRect();
          var cR = centerWrap.getBoundingClientRect();
          var cl   = { x: cR.left - dR.left, y: cR.top - dR.top, w: cR.width, h: cR.height };
          var ccy  = cl.y + cl.h / 2;
          var spread = 13;
          var ds = [];
          var i, dot, r, sx, sy, ex, ey, mx;
          for (i = 0; i < 5; i++) {
            dot = document.getElementById("tnav-ld" + i);
            if (!dot) continue;
            r = dot.getBoundingClientRect();
            sx = r.right  - dR.left; sy = r.top + r.height / 2 - dR.top;
            ex = cl.x;               ey = ccy + (i - 2) * spread;
            mx = sx + (ex - sx) * 0.55;
            ds.push("M " + sx + " " + sy + " C " + mx + " " + sy + " " + mx + " " + ey + " " + ex + " " + ey);
          }
          for (i = 0; i < 5; i++) {
            dot = document.getElementById("tnav-rd" + i);
            if (!dot) continue;
            r = dot.getBoundingClientRect();
            sx = cl.x + cl.w;        sy = ccy + (i - 2) * spread;
            ex = r.left - dR.left;   ey = r.top + r.height / 2 - dR.top;
            mx = sx + (ex - sx) * 0.45;
            ds.push("M " + sx + " " + sy + " C " + mx + " " + sy + " " + mx + " " + ey + " " + ex + " " + ey);
          }
          return ds;
        }

        function buildLines(instant) {
          lineGen++;
          var gen = lineGen;
          liveEntries = [];
          while (linesSvg.firstChild) linesSvg.removeChild(linesSvg.firstChild);
          var dots = [];
          var i;
          for (i = 0; i < 5; i++) dots.push(document.getElementById("tnav-ld" + i));
          for (i = 0; i < 5; i++) dots.push(document.getElementById("tnav-rd" + i));
          dots = dots.filter(Boolean);
          var ds = computeDs();
          ds.forEach(function(d, idx) {
            var p = document.createElementNS("http://www.w3.org/2000/svg", "path");
            p.setAttribute("d", d);
            p.setAttribute("fill", "none");
            p.setAttribute("stroke", "rgba(255,255,255,0.40)");
            p.setAttribute("stroke-width", "1.5");
            p.setAttribute("stroke-linecap", "round");
            p.setAttribute("stroke-dasharray", "320");
            p.setAttribute("stroke-dashoffset", "320");
            linesSvg.appendChild(p);
            liveEntries.push({ path: p, dot: dots[idx] });
          });
          if (instant || window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
            liveEntries.forEach(function(e) {
              e.path.setAttribute("stroke-dashoffset", "0");
              if (e.dot) e.dot.classList.add("live");
              loopParticle(linesSvg, e.path, gen);
            });
            return;
          }
          liveEntries.forEach(function(e, idx) {
            setTimeout(function() {
              if (gen !== lineGen) return;
              e.path.style.transition = "stroke-dashoffset 0.65s cubic-bezier(0.4,0,0.2,1)";
              e.path.setAttribute("stroke-dashoffset", "0");
              if (e.dot) e.dot.classList.add("live");
              setTimeout(function() { loopParticle(linesSvg, e.path, gen); }, 700);
            }, 1300 + idx * 125);
          });
        }

        function trackLines(duration) {
          if (!liveEntries.length) return;
          if (trackFrame) { cancelAnimationFrame(trackFrame); trackFrame = null; }
          var start = performance.now();
          var gen   = lineGen;
          function frame(now) {
            if (gen !== lineGen) { trackFrame = null; return; }
            var ds = computeDs();
            liveEntries.forEach(function(e, i) { if (ds[i]) e.path.setAttribute("d", ds[i]); });
            if (now - start < duration) { trackFrame = requestAnimationFrame(frame); }
            else { trackFrame = null; }
          }
          trackFrame = requestAnimationFrame(frame);
        }

        function loopParticle(svg, path, gen) {
          if (gen !== lineGen) return;
          // The page-builder animation runs continuously for the life of
          // the page (10 concurrent rAF loops). Pausing while the tab is
          // hidden avoids burning CPU/GPU in the background — same visual
          // result, less wasted work.
          if (document.hidden) {
            var resume = function () {
              document.removeEventListener("visibilitychange", resume);
              loopParticle(svg, path, gen);
            };
            document.addEventListener("visibilitychange", resume);
            return;
          }
          var c = document.createElementNS("http://www.w3.org/2000/svg", "circle");
          c.setAttribute("r", "3.5");
          c.setAttribute("fill", "#cb6715");
          c.style.opacity = "0";
          svg.appendChild(c);
          var dur = 950 + Math.random() * 350;
          var t0  = performance.now();
          var len;
          try { len = path.getTotalLength(); } catch(e) { len = 260; }
          function tick(now) {
            if (gen !== lineGen) { if (svg.contains(c)) svg.removeChild(c); return; }
            var p = Math.min((now - t0) / dur, 1);
            var e = p < 0.5 ? 2 * p * p : -1 + (4 - 2 * p) * p;
            try {
              var pt = path.getPointAtLength(e * len);
              c.setAttribute("cx", pt.x); c.setAttribute("cy", pt.y);
              c.style.opacity = p < 0.10 ? (p / 0.10).toFixed(3)
                              : p > 0.82 ? ((1 - p) / 0.18).toFixed(3) : "1";
            } catch(_) {}
            if (p < 1) { requestAnimationFrame(tick); }
            else {
              if (svg.contains(c)) svg.removeChild(c);
              setTimeout(function() { loopParticle(svg, path, gen); }, 1600 + Math.random() * 3800);
            }
          }
          requestAnimationFrame(tick);
        }

        if (phoneWrap && cardInner && flipHint) {
          phoneWrap.addEventListener("click", function() {
            cardInner.classList.toggle("flipped");
            flipHint.innerHTML = cardInner.classList.contains("flipped")
              ? "&#8635; &nbsp;tap to flip back"
              : "&#8635; &nbsp;tap for glossary";
          });
        }

        // CTA uses a normal #CTA anchor so it remains functional even if
        // this script is delayed, deferred or blocked by the live site's optimiser.
        Array.prototype.forEach.call(block.querySelectorAll(".tnav-item"), function(item) {
          item.addEventListener("click", function() {
            item.classList.toggle("open");
            trackLines(350);
          });
        });

        function init() {
          clearTimeout(resizeTimer);
          // Wait long enough for every item's entrance animation (up to ~1.36s
          // for the last "Outcome" row) to finish before measuring dot
          // positions, otherwise a connector line built mid-transition can be
          // permanently misaligned by a few pixels until the next resize.
          resizeTimer = setTimeout(function() { buildLines(false); }, 1450);
        }
        init();
        window.addEventListener("resize", function() {
          clearTimeout(resizeTimer);
          resizeTimer = setTimeout(function() { buildLines(true); }, 300);
        });
      } catch (err) {
        if (window.console && console.warn) console.warn("tnav hero:", err);
      }
    });
  } catch (err) {
    if (window.console && console.warn) console.warn("tnav hero:", err);
  }
})();
