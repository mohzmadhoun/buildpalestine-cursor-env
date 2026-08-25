(function () {
	"use strict";

	var VIDEO_ID = /^[a-zA-Z0-9_-]{11}$/;

	function createIframe(videoId, title, extraQuery) {
		var iframe = document.createElement("iframe");
		iframe.src =
			"https://www.youtube.com/embed/" +
			encodeURIComponent(videoId) +
			"?autoplay=1&rel=0&modestbranding=1&playsinline=1" +
			(extraQuery || "");
		iframe.title = title || "YouTube video";
		iframe.allow =
			"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share";
		iframe.setAttribute("allowfullscreen", "allowfullscreen");
		iframe.setAttribute("loading", "eager");
		return iframe;
	}

	function loadFacade(facade) {
		if (!facade || facade.getAttribute("data-loaded") === "1") {
			return;
		}

		var videoId = facade.getAttribute("data-video-id") || "";
		if (!VIDEO_ID.test(videoId)) {
			return;
		}

		facade.setAttribute("data-loaded", "1");
		facade.replaceChildren(
			createIframe(videoId, facade.getAttribute("data-title") || "")
		);
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

	function playInHero() {
		var hero = heroTarget();
		var videoId = heroVideoId();
		if (!hero || !videoId) {
			return false;
		}

		var section = hero.closest(".mzm-homepage-hero-section");
		if (section) {
			section.classList.add("is-playing-hero-video");
		}

		hero.setAttribute("data-loaded", "1");
		hero.replaceChildren(
			createIframe(
				videoId,
				hero.getAttribute("data-title") || "BuildPalestine video",
				"&controls=1"
			)
		);
		return true;
	}

	function stopHero() {
		var hero = heroTarget();
		if (!hero) {
			return;
		}
		hero.replaceChildren();
		hero.removeAttribute("data-loaded");
		var section = hero.closest(".mzm-homepage-hero-section");
		if (section) {
			section.classList.remove("is-playing-hero-video");
		}
	}

	document.addEventListener(
		"click",
		function (event) {
			var target = event.target;
			if (!target || !target.closest) {
				return;
			}

			if (target.closest(".open-hero-video")) {
				event.preventDefault();
				event.stopPropagation();
				if (event.stopImmediatePropagation) {
					event.stopImmediatePropagation();
				}
				playInHero();
				return;
			}

			var facade = target.closest(".bp-yt-lite");
			if (facade && !facade.closest(".mzm-homepage-hero-section")) {
				event.preventDefault();
				loadFacade(facade);
			}
		},
		true
	);

	document.addEventListener(
		"keydown",
		function (event) {
			if (event.key === "Escape") {
				stopHero();
				return;
			}
			if (event.key !== "Enter" && event.key !== " ") {
				return;
			}
			var target = event.target;
			if (!target || !target.closest) {
				return;
			}
			if (target.closest(".open-hero-video")) {
				event.preventDefault();
				playInHero();
				return;
			}
			var facade = target.closest(".bp-yt-lite");
			if (facade && !facade.closest(".mzm-homepage-hero-section")) {
				event.preventDefault();
				loadFacade(facade);
			}
		},
		true
	);
})();
