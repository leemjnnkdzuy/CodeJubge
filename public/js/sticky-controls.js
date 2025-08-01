document.addEventListener("DOMContentLoaded", function () {
	const discussionsControls = document.querySelector(".discussions-controls");
	if (!discussionsControls) return;

	const originalOffsetTop = discussionsControls.offsetTop;
	const controlsHeight = discussionsControls.offsetHeight;
	const originalWidth = discussionsControls.offsetWidth;

	const originalStyles = {
		width: discussionsControls.style.width || "",
		boxSizing: discussionsControls.style.boxSizing || "",
		padding: discussionsControls.style.padding || "",
	};

	const placeholder = document.createElement("div");
	placeholder.style.height = controlsHeight + "px";
	placeholder.style.display = "none";
	discussionsControls.parentNode.insertBefore(placeholder, discussionsControls);

	function handleScroll() {
		const scrollY = window.scrollY || window.pageYOffset;

		if (scrollY > originalOffsetTop) {
			if (!discussionsControls.classList.contains("fixed-controls")) {
				const containerWidth = discussionsControls.parentNode.clientWidth;

				discussionsControls.classList.add("fixed-controls");
				placeholder.style.display = "block";
				adjustForSidebar();
			}
		} else {
			if (discussionsControls.classList.contains("fixed-controls")) {
				discussionsControls.classList.remove("fixed-controls");
				placeholder.style.display = "none";

				discussionsControls.style.width = originalStyles.width;
				discussionsControls.style.boxSizing = originalStyles.boxSizing;
				discussionsControls.style.padding = originalStyles.padding;
				discussionsControls.style.left = "";
				discussionsControls.style.right = "";
			}
		}
	}

	function adjustForSidebar() {
		const sidebar = document.getElementById("sidebar");
		const mainContent = document.getElementById("mainContent");
		const contentWrapper = document.querySelector(".content-wrapper");

		const containerWidth = contentWrapper
			? contentWrapper.clientWidth
			: document.querySelector(".discussions-container").clientWidth;

		if (sidebar && mainContent) {
			const isSidebarCollapsed = sidebar.classList.contains("collapsed");
			const isMobile = window.innerWidth <= 768;

			if (isMobile) {
				if (mainContent.classList.contains("sidebar-collapsed")) {
					discussionsControls.style.left = "90px";
					discussionsControls.style.width = "calc(100% - 90px)";
				} else {
					discussionsControls.style.left = "0";
					discussionsControls.style.width = "100%";
				}
			} else {
				if (isSidebarCollapsed) {
					discussionsControls.style.left = "90px";
					discussionsControls.style.width = containerWidth + "px";
				} else {
					discussionsControls.style.left = "280px";
					discussionsControls.style.width = containerWidth + "px";
				}
			}
		}
	}

	window.addEventListener("scroll", handleScroll);

	window.addEventListener("resize", function () {
		placeholder.style.height = discussionsControls.offsetHeight + "px";

		if (discussionsControls.classList.contains("fixed-controls")) {
			adjustForSidebar();
		} else {
			discussionsControls.style.width = originalStyles.width;
			discussionsControls.style.boxSizing = originalStyles.boxSizing;
		}
	});

	document.addEventListener("click", function (e) {
		if (e.target.closest(".sidebar-toggle")) {
			setTimeout(function () {
				if (discussionsControls.classList.contains("fixed-controls")) {
					adjustForSidebar();
				}
			}, 300);
		}
	});

	handleScroll();
});
