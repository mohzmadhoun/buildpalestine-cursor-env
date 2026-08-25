(function () {
	"use strict";

	var VIDEO_ID = /^[a-zA-Z0-9_-]{11}$/;

	function createIframe(videoId, title, query) {
		var iframe = document.createElement("iframe");
		iframe.src =
			"https://www.youtube.com/embed/" +
			encodeURIComponent(videoId) +
			"?" +
			query;
		iframe.title = title || "YouTube video";
		iframe.allow =
			"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share";
		iframe.setAttribute("allowfullscreen", "allowfullscreen");
		iframe.setAttribute("loading", "eager");
		return iframe;
	}

	function heroBackgroundQuery(videoId) {
		return (
			"autoplay=1&mute=1&controls=0&loop=1&playlist=" +
			encodeURIComponent(videoId) +
			"&modestbranding=1&playsinline=1&rel=0"
		);
	}

	function watchQuery() {
		return "autoplay=1&rel=0&modestbranding=1&playsinline=1&controls=1";
	}

	function heroTarget() {
		return document.getElementById("yt-player");
	}

	function heroVideoId() {
		var hero = heroTarget();
		if (hero) {
			var fromHero = hero.getAttribute("data-video-id") || "";
			if (VIDEO_ID.test(fromHero)) {
				return fromHero;
			}
		}
		var lite = document.querySelector(".bp-yt-lite[data-video-id]");
		if (lite) {
			var fromLite = lite.getAttribute("data-video-id") || "";
			if (VIDEO_ID.test(fromLite)) {
				return fromLite;
			}
		}
		return "";
	}

	function isPopupOpen() {
		return !!document.querySelector(
			".eb-popup-overlay.active, .modal-main-wrap.active"
		);
	}

	function startHeroBackground() {
		if (isPopupOpen()) {
			return;
		}

		var hero = heroTarget();
		var videoId = heroVideoId();
		if (!hero || !videoId || hero.getAttribute("data-loaded") === "bg") {
			return;
		}

		hero.setAttribute("data-loaded", "bg");
		var iframe = createIframe(
			videoId,
			hero.getAttribute("data-title") || "BuildPalestine video",
			heroBackgroundQuery(videoId)
		);
		hero.replaceChildren(iframe);
		watchHeroCover(iframe);
	}

	function stopHeroBackground() {
		var hero = heroTarget();
		if (!hero) {
			return;
		}
		hero.replaceChildren();
		hero.removeAttribute("data-loaded");
	}

	function loadFacade(facade) {
		if (!facade || facade.getAttribute("data-loaded") === "1") {
			return;
		}

		var videoId = facade.getAttribute("data-video-id") || "";
		if (!VIDEO_ID.test(videoId)) {
			return;
		}

		if (!facade.getAttribute("data-original-html")) {
			facade.setAttribute("data-original-html", facade.innerHTML);
		}

		facade.setAttribute("data-loaded", "1");
		facade.replaceChildren(
			createIframe(
				videoId,
				facade.getAttribute("data-title") || "",
				watchQuery()
			)
		);
	}

	function loadPopupFacades() {
		document
			.querySelectorAll(".eb-popup-content .bp-yt-lite")
			.forEach(loadFacade);
	}

	function applyHeroCover(iframe) {
		if (!iframe || !iframe.parentElement) {
			return;
		}

		var host =
			document.querySelector(".mzm-background-video") || iframe.parentElement;
		var width = host.clientWidth;
		var height = host.clientHeight;
		if (!width || !height) {
			return;
		}

		var coverWidth = Math.max(width, (height * 16) / 9);
		var coverHeight = Math.max(height, (width * 9) / 16);

		iframe.style.setProperty("position", "absolute", "important");
		iframe.style.setProperty("top", "50%", "important");
		iframe.style.setProperty("left", "50%", "important");
		iframe.style.setProperty("width", coverWidth + "px", "important");
		iframe.style.setProperty("height", coverHeight + "px", "important");
		iframe.style.setProperty("max-width", "none", "important");
		iframe.style.setProperty("max-height", "none", "important");
		iframe.style.setProperty("min-width", "0", "important");
		iframe.style.setProperty("min-height", "0", "important");
		iframe.style.setProperty("border", "0", "important");
		iframe.style.setProperty("transform", "translate(-50%, -50%)", "important");
		iframe.style.pointerEvents = "none";
	}

	function watchHeroCover(iframe) {
		applyHeroCover(iframe);

		var host =
			document.querySelector(".mzm-background-video") || iframe.parentElement;
		if (!host) {
			return;
		}

		if (host._bpYtCover && typeof host._bpYtCover.disconnect === "function") {
			host._bpYtCover.disconnect();
		}

		if (typeof ResizeObserver === "function") {
			var observer = new ResizeObserver(function () {
				applyHeroCover(iframe);
			});
			host._bpYtCover = observer;
			observer.observe(host);
			return;
		}

		window.addEventListener("resize", function () {
			applyHeroCover(iframe);
		});
	}

	function preparePopupLayout() {
		document.querySelectorAll(".eb-popup-content").forEach(function (content) {
			if (content.querySelector(".bp-yt-lite")) {
				content.classList.add("bp-yt-popup-content");
			}
		});
	}

	function onPopupOpened() {
		preparePopupLayout();
		stopHeroBackground();
		loadPopupFacades();
	}

	function onPopupClosed() {
		document
			.querySelectorAll(".eb-popup-content .bp-yt-lite[data-loaded]")
			.forEach(function (facade) {
				var original = facade.getAttribute("data-original-html");
				facade.removeAttribute("data-loaded");
				if (original) {
					facade.innerHTML = original;
				} else {
					facade.replaceChildren();
				}
			});
		startHeroBackground();
	}

	if (typeof MutationObserver === "function") {
		var popupWasOpen = false;
		var observer = new MutationObserver(function () {
			var open = isPopupOpen();
			if (open && !popupWasOpen) {
				onPopupOpened();
			} else if (!open && popupWasOpen) {
				onPopupClosed();
			}
			popupWasOpen = open;
		});

		document.addEventListener("DOMContentLoaded", function () {
			preparePopupLayout();
			document
				.querySelectorAll(".eb-popup-overlay, .modal-main-wrap")
				.forEach(function (el) {
					observer.observe(el, {
						attributes: true,
						attributeFilter: ["class"],
					});
				});
		});
	}

	function scheduleHeroAutoplay() {
		var started = false;
		function startOnce() {
			if (started) {
				return;
			}
			started = true;
			startHeroBackground();
		}

		if ("requestIdleCallback" in window) {
			window.requestIdleCallback(startOnce, { timeout: 1200 });
		} else {
			window.addEventListener("load", startOnce);
			window.setTimeout(startOnce, 1200);
		}
	}

	scheduleHeroAutoplay();
})();
