(() => {
  const html = document.documentElement;
  const darkToggle = document.getElementById("darkToggle");
  const darkToggleMobile = document.getElementById("darkToggleMobile");
  const moonIcon = document.getElementById("moon");
  const sunIcon = document.getElementById("sun");
  const moonMobile = document.getElementById("moonMobile");
  const countdownEl = document.getElementById("countdown");
  const countdownElMobile = document.getElementById("countdownmobile");

  const savedTheme = localStorage.getItem("theme");
  const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
  if (savedTheme === "dark" || (!savedTheme && prefersDark))
    html.classList.add("dark");

  function refreshIcons() {
    const isDark = html.classList.contains("dark");
    moonIcon.classList.toggle("hidden", isDark);
    sunIcon.classList.toggle("hidden", !isDark);
    const pathEl = moonMobile.querySelector("path");
    pathEl.setAttribute(
      "d",
      isDark
        ? "M12 3v1m0 16v1m8.485-8.485l.707.707M3.515 3.515l.707.707M21 12h1M2 12H1m15.364 6.364l.707.707M6.343 6.343l.707.707M4 12a8 8 0 1016 0 8 8 0 00-16 0z"
        : "M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"
    );
  }

  function toggleDarkMode() {
    html.classList.toggle("dark");
    localStorage.setItem(
      "theme",
      html.classList.contains("dark") ? "dark" : "light"
    );
    refreshIcons();
  }

  darkToggle?.addEventListener("click", toggleDarkMode);
  darkToggleMobile?.addEventListener("click", toggleDarkMode);
  refreshIcons();

  const target = new Date("2025-12-01T00:00:00");
  const pad = (n) => (n < 10 ? "0" + n : n);
  const updateCountdown = (el) => {
    const diff = target - new Date();
    if (diff <= 0) return (el.textContent = "🎉 SUDGEC 2025 is live!");
    const days = Math.floor(diff / 86400000),
      hours = Math.floor((diff / 3600000) % 24),
      mins = Math.floor((diff / 60000) % 60),
      secs = Math.floor((diff / 1000) % 60);
    el.textContent = `${pad(days)}d : ${pad(hours)}h : ${pad(mins)}m : ${pad(
      secs
    )}s`;
  };
  if (countdownEl) setInterval(() => updateCountdown(countdownEl), 1000);
  if (countdownElMobile)
    setInterval(() => updateCountdown(countdownElMobile), 1000);

  document.getElementById("mobileToggle")?.addEventListener("click", () => {
    document.getElementById("mobileNav").classList.toggle("hidden");
  });
})();

document.getElementById("year").textContent = new Date().getFullYear();
