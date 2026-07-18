/* ============================================================
   AVÉA Creator Hub — Onboarding (script.js)
   Accordion timeline + progress · vanilla JS
   ============================================================ */

(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {

    /* ---------- Button navigation ---------- */
    document.querySelectorAll(".btn[data-href]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var target = btn.getAttribute("data-href");
        if (target) window.location.href = target;
      });
    });

    /* ---------- Accordion timeline ---------- */
    var steps    = Array.prototype.slice.call(document.querySelectorAll(".tl-step"));
    var fill     = document.getElementById("pFill");
    var counter  = document.getElementById("pNow");
    var bar      = document.querySelector(".progress");
    var total    = steps.length;

    function setBody(step, open) {
      var body = step.querySelector(".tl-body");
      body.style.maxHeight = open ? body.scrollHeight + "px" : null;
    }

    function openStep(index) {
      steps.forEach(function (step, i) {
        var isOpen = i === index;
        step.classList.toggle("is-open", isOpen);
        step.classList.toggle("done", i < index);
        step.querySelector(".tl-head").setAttribute("aria-expanded", isOpen);
        setBody(step, isOpen);
      });

      var pct = ((index + 1) / total) * 100;
      fill.style.width = pct + "%";
      counter.textContent = index + 1;
      bar.setAttribute("aria-valuenow", index + 1);
    }

    steps.forEach(function (step, i) {
      step.querySelector(".tl-head").addEventListener("click", function () {
        // clicking the open step collapses it back to the first
        openStep(step.classList.contains("is-open") ? 0 : i);
      });
    });

    openStep(0);

    /* keep heights correct on resize */
    var resizeTimer;
    window.addEventListener("resize", function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function () {
        var open = document.querySelector(".tl-step.is-open");
        if (open) setBody(open, true);
      }, 120);
    });

  });
})();