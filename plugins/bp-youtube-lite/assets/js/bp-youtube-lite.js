(function () {
	"use strict";

	function loadPlayer(facade) {
		if (!facade || facade.getAttribute("data-loaded") === "1") {
			return;
		}

		var videoId = facade.getAttribute("data-video-id") || "";
		if (!/^[a-zA-Z0-9_-]{11}$/.test(videoId)) {
			return;
		}

		facade.setAttribute("data-loaded", "1");

		var iframe = document.createElement("iframe");
		iframe.src =
			"https://www.youtube-nocookie.com/embed/" +
			encodeURIComponent(videoId) +
			"?autoplay=1&rel=0&modestbranding=1&playsinline=1";
		iframe.title = facade.getAttribute("data-title") || "YouTube video";
		iframe.allow =
			"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share";
		iframe.setAttribute("allowfullscreen", "allowfullscreen");
		iframe.setAttribute("loading", "eager");

		facade.replaceChildren(iframe);
	}

	function loadFacadesIn(root) {
		if (!root || !root.querySelectorAll) {
			return;
		}
		root.querySelectorAll(".bp-yt-lite").forEach(loadPlayer);
	}

	document.addEventListener(
		"click",
		function (event) {
			var target = event.target;
			if (!target || !target.closest) {
				return;
			}

			var facade = target.closest(".bp-yt-lite");
			if (facade) {
				event.preventDefault();
				loadPlayer(facade);
				return;
			}

			if (target.closest(".open-hero-video")) {
				window.requestAnimationFrame(function () {
					document
						.querySelectorAll(".eb-popup-content .bp-yt-lite")
						.forEach(loadPlayer);
				});
			}
		},
		false
	);

	document.addEventListener(
		"keydown",
		function (event) {
			if (event.key !== "Enter" && event.key !== " ") {
				return;
			}
			var target = event.target;
			if (!target || !target.closest) {
				return;
			}
			var facade = target.closest(".bp-yt-lite");
			if (!facade) {
				return;
			}
			event.preventDefault();
			loadPlayer(facade);
		},
		false
	);

	if (typeof MutationObserver === "function") {
		var observer = new MutationObserver(function (mutations) {
			mutations.forEach(function (mutation) {
				if (mutation.type !== "attributes") {
					return;
				}
				var el = mutation.target;
				if (!el || !el.classList || !el.classList.contains("eb-popup-container")) {
					return;
				}
				if (el.classList.contains("active") || el.classList.contains("show")) {
					loadFacadesIn(el);
				}
			});
		});

		document.addEventListener("DOMContentLoaded", function () {
			document.querySelectorAll(".eb-popup-container").forEach(function (popup) {
				observer.observe(popup, { attributes: true, attributeFilter: ["class"] });
			});
		});
	}
})();
