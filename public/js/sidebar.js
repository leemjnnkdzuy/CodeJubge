function toggleSidebar() {
	const sidebar = document.getElementById("sidebar");
	const mainContent = document.querySelector(".main-content");

	if (sidebar && mainContent) {
		sidebar.classList.toggle("collapsed");
		mainContent.classList.toggle("sidebar-collapsed");
	}
}

document.addEventListener("DOMContentLoaded", function () {
	const isCollapsed = localStorage.getItem("sidebarCollapsed") === "true";

	if (isCollapsed) {
		const sidebar = document.getElementById("sidebar");
		const mainContent = document.querySelector(".main-content");

		if (sidebar && mainContent) {
			sidebar.classList.add("collapsed");
			mainContent.classList.add("sidebar-collapsed");
		}
	}
});

function toggleSidebar() {
	const sidebar = document.getElementById("sidebar");
	const mainContent = document.querySelector(".main-content");

	if (sidebar && mainContent) {
		sidebar.classList.toggle("collapsed");
		mainContent.classList.toggle("sidebar-collapsed");

		const isCollapsed = sidebar.classList.contains("collapsed");
		localStorage.setItem("sidebarCollapsed", isCollapsed.toString());
	}
}
