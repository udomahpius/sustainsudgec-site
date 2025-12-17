
  const bannerOverlay = document.getElementById("bannerOverlay");
  const bannerPopup = document.getElementById("bannerPopup");

  function openBanner() {
    bannerOverlay.classList.remove("hidden");
    bannerOverlay.classList.add("flex");

    setTimeout(() => {
      bannerPopup.classList.add("show-banner");
    }, 50);
  }

  function closeBanner() {
    bannerPopup.classList.remove("show-banner");

    setTimeout(() => {
      bannerOverlay.classList.add("hidden");
      bannerOverlay.classList.remove("flex");
    }, 300);
  }

  // 🔥 Auto-show on page load (optional)
  window.addEventListener("load", () => {
    setTimeout(openBanner, 1200);
  });

