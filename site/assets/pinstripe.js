/* Pinstripe front page: the tape, the fitting, the pins, the copy button and
   one thing the tailor says when asked. Plain JavaScript, no build step.

   Nothing here is needed to read the page. Without motion (or without this
   file) the fitting hangs both garments side by side, already chalked and
   pressed, and the tape stays in its drawer. Measure twice, script once. */
(function () {
  "use strict";

  var root = document.documentElement;
  var motion = root.classList.contains("motion");

  /* ------------------------------------------------------- copy button */
  document.querySelectorAll("[data-copy]").forEach(function (btn) {
    var label = btn.querySelector(".copy-btn__label");
    var original = label ? label.textContent : "";
    btn.addEventListener("click", function () {
      var text = btn.getAttribute("data-copy");
      var done = function (ok) {
        if (!label) return;
        label.textContent = ok ? "Copied. Folded and pressed." : "Select and copy";
        window.setTimeout(function () { label.textContent = original; }, 2200);
      };
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function () { done(true); }, function () { done(false); });
      } else {
        done(false);
      }
    });
  });

  /* ----------------------------------------------- pins in the cushion */
  var pins = document.querySelectorAll(".cpin");
  document.querySelectorAll(".order__row").forEach(function (row) {
    var pin = row.getAttribute("data-pin");
    var target = null;
    pins.forEach(function (p) { if (p.getAttribute("data-pin") === pin) target = p; });
    if (!target) return;
    var up = function () { target.classList.add("is-up"); };
    var down = function () { target.classList.remove("is-up"); };
    row.addEventListener("mouseenter", up);
    row.addEventListener("mouseleave", down);
    row.addEventListener("focusin", up);
    row.addEventListener("focusout", down);
  });

  /* ------------------------------------------- the tailor answers back */
  var toast = document.querySelector(".toast");
  var typed = "";
  var toastTimer = 0;
  document.addEventListener("keydown", function (e) {
    if (!toast || e.ctrlKey || e.metaKey || e.altKey || e.key.length !== 1) return;
    typed = (typed + e.key.toLowerCase()).slice(-7);
    if (typed === "measure") {
      toast.textContent = "Taking your measurements: cut PER, inseam level 8, lined with Larastan. You'll look sharp.";
      window.clearTimeout(toastTimer);
      toastTimer = window.setTimeout(function () { toast.textContent = ""; }, 4500);
    }
  });

  if (!motion) return;

  /* --------------------------------------------------- scroll reveals */
  var rail = document.querySelector(".rail");
  if (rail) {
    rail.querySelectorAll(".hanger").forEach(function (h, i) { h.style.setProperty("--i", i); });
  }
  var revealables = document.querySelectorAll("[data-reveal], .rail");
  if ("IntersectionObserver" in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add("is-in");
          io.unobserve(entry.target);
        }
      });
    }, { rootMargin: "0px 0px -10% 0px", threshold: 0.08 });
    revealables.forEach(function (el) { io.observe(el); });
  } else {
    revealables.forEach(function (el) { el.classList.add("is-in"); });
  }

  /* ------------------------------------------------------------- tape */
  var tape = document.querySelector(".tape");
  var roll = tape && tape.querySelector(".tape__roll");
  var ticks = tape && tape.querySelector(".tape__ticks");
  var read = tape && tape.querySelector(".tape__level");
  var lastLevel = -1;
  var readTimer = 0;

  function numberTicks() {
    if (!ticks) return;
    ticks.querySelectorAll(".tape__n").forEach(function (el) { el.remove(); });
    var n = Math.ceil(window.innerWidth / 40) + 1;
    for (var i = 1; i < n; i++) {
      var s = document.createElement("span");
      s.className = "tape__n";
      s.style.left = i * 40 + "px";
      s.textContent = String(i);
      ticks.appendChild(s);
    }
  }

  var LEVEL_WORDS = { 8: "level 8 · our fit", 9: "level 9 · snug", 10: "level 10 · can't breathe" };

  function updateTape() {
    if (!roll) return;
    var max = document.documentElement.scrollHeight - window.innerHeight;
    var p = max > 0 ? Math.min(1, Math.max(0, window.scrollY / max)) : 0;
    roll.style.width = (p * 100).toFixed(2) + "%";
    tape.style.setProperty("--tape-x", Math.max(90, p * window.innerWidth) + "px");
    var level = Math.round(p * 10);
    if (level !== lastLevel) {
      lastLevel = level;
      read.textContent = LEVEL_WORDS[level] || "level " + level;
      tape.classList.add("is-reading");
      window.clearTimeout(readTimer);
      readTimer = window.setTimeout(function () { tape.classList.remove("is-reading"); }, 1400);
    }
  }

  /* ---------------------------------------------------------- fitting */
  var fitting = document.querySelector("[data-fitting]");
  var garments = fitting && fitting.querySelector(".garments");
  var marks = fitting ? fitting.querySelectorAll(".chalk[data-step]") : [];
  var notes = fitting ? fitting.querySelectorAll(".ticket__list li[data-step]") : [];
  var done = fitting && fitting.querySelector(".ticket__done");
  var steps = fitting ? fitting.querySelectorAll("[data-step-label]") : [];
  var STEPS = 5;
  var tall = window.matchMedia("(min-width: 900px) and (min-height: 640px), (max-width: 899.98px) and (min-height: 700px)");

  function setLive() {
    if (!fitting) return;
    fitting.classList.toggle("is-live", tall.matches);
    if (!tall.matches) {
      marks.forEach(function (m) { m.classList.add("is-on"); });
    }
  }

  function updateFitting() {
    if (!fitting || !fitting.classList.contains("is-live")) return;
    var rect = fitting.getBoundingClientRect();
    var run = rect.height - window.innerHeight;
    var p = run > 0 ? Math.min(1, Math.max(0, -rect.top / run)) : 0;

    // 0 – 0.1: as it walked in. 0.1 – 0.55: chalk, one mark at a time.
    // 0.55 – 0.82: the iron passes. After that: pressed.
    var chalked = p < 0.1 ? 0 : Math.min(STEPS, Math.ceil(((p - 0.1) / 0.45) * STEPS));
    var press = p < 0.55 ? 0 : Math.min(1, (p - 0.55) / 0.27);

    marks.forEach(function (m) {
      m.classList.toggle("is-on", Number(m.getAttribute("data-step")) <= chalked);
    });
    notes.forEach(function (n) {
      var s = Number(n.getAttribute("data-step"));
      n.classList.toggle("is-on", s <= chalked);
      n.classList.toggle("is-now", s === chalked && press === 0);
    });
    garments.style.setProperty("--press", press.toFixed(4));
    garments.classList.toggle("is-pressing", press > 0 && press < 1);
    if (done) done.classList.toggle("is-on", press >= 1);

    var phase = press >= 1 ? 2 : chalked > 0 ? 1 : 0;
    steps.forEach(function (li) {
      li.classList.toggle("is-on", Number(li.getAttribute("data-step-label")) <= phase);
    });
  }

  var ticking = false;
  function onScroll() {
    if (ticking) return;
    ticking = true;
    window.requestAnimationFrame(function () {
      ticking = false;
      updateTape();
      updateFitting();
    });
  }

  numberTicks();
  setLive();
  updateTape();
  updateFitting();
  window.addEventListener("scroll", onScroll, { passive: true });
  window.addEventListener("resize", function () {
    numberTicks();
    setLive();
    onScroll();
  });
  if (tall.addEventListener) tall.addEventListener("change", function () { setLive(); onScroll(); });
})();
