document.addEventListener("DOMContentLoaded", function () {
	const toggleBtn = document.getElementById("toggleTypesBtn");
	const toggleText = document.getElementById("toggleText");
	const toggleIcon = document.getElementById("toggleIcon");
	const hiddenItems = document.querySelectorAll(".problem-type-item.hidden");
	const searchBtn = document.getElementById("searchBtn");
	const searchInput = document.getElementById("searchInput");
	const headerSticky = document.querySelector(".problems-header-sticky");
	const applyFiltersBtn = document.getElementById("applyFiltersBtn");

	let isExpanded = false;
	let currentFilters = {
		search: "",
		difficulty: [],
		problem_types: [],
		status: "all",
		page: 1,
	};

	// Sticky header functionality
	const scrollTrigger = document.createElement("div");
	scrollTrigger.style.position = "absolute";
	scrollTrigger.style.top = "200px";
	scrollTrigger.style.height = "1px";
	scrollTrigger.style.width = "100%";
	scrollTrigger.style.pointerEvents = "none";
	document.body.appendChild(scrollTrigger);

	const observer = new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				if (headerSticky) {
					const isMobile = window.innerWidth <= 768;

					if (!entry.isIntersecting) {
						if (isMobile) {
							headerSticky.style.setProperty("--header-scale", "0.85");
							headerSticky.style.setProperty("--header-y", "-25px");
							headerSticky.style.setProperty("--header-padding", "0.5rem");
							headerSticky.style.setProperty("--title-size", "1.5rem");
							headerSticky.style.setProperty("--subtitle-size", "0.85rem");
							headerSticky.style.setProperty("--input-height", "36px");
						} else {
							headerSticky.style.setProperty("--header-scale", "0.8");
							headerSticky.style.setProperty("--header-y", "-40px");
							headerSticky.style.setProperty("--header-padding", "0.75rem 0");
							headerSticky.style.setProperty("--title-size", "1.8rem");
							headerSticky.style.setProperty("--subtitle-size", "0.9rem");
							headerSticky.style.setProperty("--input-height", "38px");
						}
						headerSticky.style.background = "rgba(248, 249, 250, 0.98)";
						headerSticky.style.boxShadow = "0 4px 20px rgba(0, 0, 0, 0.15)";
						headerSticky.style.borderBottom = "1px solid rgba(0, 0, 0, 0.1)";
					} else {
						headerSticky.style.setProperty("--header-scale", "1");
						headerSticky.style.setProperty("--header-y", "0px");

						if (isMobile) {
							headerSticky.style.setProperty("--header-padding", "1rem");
							headerSticky.style.setProperty("--title-size", "2rem");
							headerSticky.style.setProperty("--subtitle-size", "1rem");
							headerSticky.style.setProperty("--input-height", "40px");
						} else {
							headerSticky.style.setProperty("--header-padding", "1.5rem 0");
							headerSticky.style.setProperty("--title-size", "2.5rem");
							headerSticky.style.setProperty("--subtitle-size", "1.1rem");
							headerSticky.style.setProperty("--input-height", "44px");
						}

						headerSticky.style.background = "";
						headerSticky.style.boxShadow = "";
						headerSticky.style.borderBottom = "";
					}
				}
			});
		},
		{
			threshold: 0,
			rootMargin: "0px",
		}
	);

	observer.observe(scrollTrigger);

	// Toggle problem types
	if (toggleBtn) {
		toggleBtn.addEventListener("click", function () {
			isExpanded = !isExpanded;

			hiddenItems.forEach((item) => {
				if (isExpanded) {
					item.classList.remove("hidden");
				} else {
					item.classList.add("hidden");
				}
			});

			toggleText.textContent = isExpanded ? "Ẩn bớt đi" : "Xem nhiều hơn";
			toggleIcon.classList.toggle("bx-chevron-up", isExpanded);
			toggleIcon.classList.toggle("bx-chevron-down", !isExpanded);
		});
	}

	// Search functionality
	function performSearch() {
		currentFilters.search = searchInput.value.trim();
		currentFilters.page = 1;
		applyFilters();
	}

	if (searchBtn) {
		searchBtn.addEventListener("click", performSearch);
	}

	if (searchInput) {
		searchInput.addEventListener("keypress", function (e) {
			if (e.key === "Enter") {
				performSearch();
			}
		});
	}

	// Problem type selection
	document.querySelectorAll(".problem-type-item").forEach((item) => {
		item.addEventListener("click", function () {
			const type = this.dataset.type;
			this.classList.toggle("active");

			if (this.classList.contains("active")) {
				if (!currentFilters.problem_types.includes(type)) {
					currentFilters.problem_types.push(type);
				}
			} else {
				currentFilters.problem_types = currentFilters.problem_types.filter(
					(t) => t !== type
				);
			}
		});
	});

	// Difficulty filters
	document.querySelectorAll(".difficulty-filter input").forEach((checkbox) => {
		checkbox.addEventListener("change", function () {
			const difficulty = this.value;

			if (this.checked) {
				if (!currentFilters.difficulty.includes(difficulty)) {
					currentFilters.difficulty.push(difficulty);
				}
			} else {
				currentFilters.difficulty = currentFilters.difficulty.filter(
					(d) => d !== difficulty
				);
			}
		});
	});

	// Status filters
	document.querySelectorAll(".status-filter input").forEach((checkbox) => {
		checkbox.addEventListener("change", function () {
			const status = this.value;

			// Handle radio-like behavior for status
			if (this.checked) {
				// Uncheck other status filters
				document.querySelectorAll(".status-filter input").forEach((cb) => {
					if (cb !== this) cb.checked = false;
				});
				currentFilters.status = status;
			}
		});
	});

	// Apply filters
	function applyFilters() {
		const params = new URLSearchParams();

		if (currentFilters.search) {
			params.append("search", currentFilters.search);
		}

		if (currentFilters.difficulty.length > 0) {
			params.append("difficulty", currentFilters.difficulty.join(","));
		}

		if (currentFilters.problem_types.length > 0) {
			params.append("problem_types", currentFilters.problem_types.join(","));
		}

		if (currentFilters.status !== "all") {
			params.append("status", currentFilters.status);
		}

		params.append("page", currentFilters.page);

		// Reload page with new filters
		window.location.href = "/problems?" + params.toString();
	}

	// Apply filters button
	if (applyFiltersBtn) {
		applyFiltersBtn.addEventListener("click", function () {
			currentFilters.page = 1;
			applyFilters();
		});
	}

	// Problem item click to view details
	document.querySelectorAll(".problem-item").forEach((item) => {
		item.addEventListener("click", function () {
			const slug = this.dataset.slug;
			if (slug) {
				window.location.href = `/problems/${slug}`;
			}
		});
	});

	// Initialize filters from URL
	function initializeFiltersFromURL() {
		const urlParams = new URLSearchParams(window.location.search);

		currentFilters.search = urlParams.get("search") || "";
		currentFilters.difficulty = urlParams.get("difficulty")
			? urlParams.get("difficulty").split(",")
			: [];
		currentFilters.problem_types = urlParams.get("problem_types")
			? urlParams.get("problem_types").split(",")
			: [];
		currentFilters.status = urlParams.get("status") || "all";
		currentFilters.page = parseInt(urlParams.get("page")) || 1;

		// Set search input value
		if (searchInput && currentFilters.search) {
			searchInput.value = currentFilters.search;
		}

		// Set difficulty checkboxes
		document.querySelectorAll(".difficulty-filter input").forEach((checkbox) => {
			checkbox.checked = currentFilters.difficulty.includes(checkbox.value);
		});

		// Set status checkboxes
		document.querySelectorAll(".status-filter input").forEach((checkbox) => {
			checkbox.checked = checkbox.value === currentFilters.status;
		});

		// Set problem type selections
		document.querySelectorAll(".problem-type-item").forEach((item) => {
			const type = item.dataset.type;
			if (currentFilters.problem_types.includes(type)) {
				item.classList.add("active");
			}
		});
	}

	// Initialize on page load
	initializeFiltersFromURL();
});

// Global function for pagination
function changePage(page) {
	const urlParams = new URLSearchParams(window.location.search);
	urlParams.set("page", page);
	window.location.href = "/problems?" + urlParams.toString();
}
