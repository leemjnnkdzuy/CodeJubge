let editingUserId = null;
let currentUserData = null;
let selectedBadges = [];
let originalFormData = {};

document.addEventListener("DOMContentLoaded", function () {
	initializeAdminUsers();
});

function initializeAdminUsers() {
	setupModalEvents();
	setupFormValidation();
	setupBadgeSelection();

	const createUserBtn = document.querySelector(".create-user-btn");
	if (createUserBtn) {
		createUserBtn.addEventListener("click", openCreateModal);
	}

	initializeTableEventListeners();
}

function setupModalEvents() {
	const modal = document.getElementById("userModal");
	const closeBtn = modal.querySelector(".edit-profile-modal-close");

	if (closeBtn) {
		closeBtn.addEventListener("click", closeEditModal);
	}

	modal.addEventListener("click", function (e) {
		if (e.target === modal) {
			closeEditModal();
		}
	});

	document.addEventListener("keydown", function (e) {
		if (e.key === "Escape" && modal.classList.contains("show")) {
			closeEditModal();
		}
	});
}

function setupFormValidation() {
	const form = document.getElementById("userForm");
	if (form) {
		form.addEventListener("submit", handleFormSubmit);

		const inputs = form.querySelectorAll(
			"input[required], input, textarea, select"
		);
		inputs.forEach((input) => {
			if (input.hasAttribute("required")) {
				input.addEventListener("blur", validateField);
			}
			input.addEventListener("input", clearFieldError);
			input.addEventListener("input", checkFormChanges);
			input.addEventListener("change", checkFormChanges);
		});

		setTimeout(() => checkFormChanges(), 100);
	}
}

function setupBadgeSelection() {
	const badgeItems = document.querySelectorAll(".badge-item.selectable");

	badgeItems.forEach((badge) => {
		const newBadge = badge.cloneNode(true);
		badge.parentNode.replaceChild(newBadge, badge);
	});

	const freshBadgeItems = document.querySelectorAll(".badge-item.selectable");
	freshBadgeItems.forEach((badge) => {
		badge.addEventListener("click", function () {
			const badgeKey = this.getAttribute("data-badge");
			toggleBadgeSelection(badgeKey, this);
		});
	});

	setupBadgesToggle();
}

function setupBadgesToggle() {
	const toggleBtn = document.getElementById("badgesToggle");
	if (toggleBtn) {
		const newToggleBtn = toggleBtn.cloneNode(true);
		toggleBtn.parentNode.replaceChild(newToggleBtn, toggleBtn);

		newToggleBtn.addEventListener("click", function () {
			const hiddenBadges = document.querySelectorAll(".badge-item.badge-hidden");
			const toggleText = this.querySelector(".toggle-text");
			const toggleIcon = this.querySelector(".toggle-icon");

			const isExpanded = this.classList.contains("expanded");

			if (isExpanded) {
				hiddenBadges.forEach((badge) => {
					badge.style.display = "none";
				});
				toggleText.textContent = "Show more";
				this.classList.remove("expanded");
			} else {
				hiddenBadges.forEach((badge) => {
					badge.style.display = "flex";
				});
				toggleText.textContent = "Show less";
				this.classList.add("expanded");
			}
		});
	}
}

function openCreateModal() {
	editingUserId = null;
	currentUserData = null;
	selectedBadges = [];
	originalFormData = {};

	resetForm();

	document.getElementById("modalTitle").textContent = "Thêm User Mới";

	const passwordGroup = document.getElementById("passwordGroup");
	if (passwordGroup) {
		passwordGroup.style.display = "block";
		const passwordInput = document.getElementById("password");
		passwordInput.required = true;
		passwordInput.placeholder = "Nhập mật khẩu";
		const note = passwordGroup.querySelector(".form-note");
		if (note) note.textContent = "Mật khẩu là bắt buộc cho user mới";
	}

	showModal();

	setTimeout(() => {
		toggleSubmitButton(true);
	}, 100);
}

function openEditModal(userId) {
	editingUserId = userId;

	document.getElementById("modalTitle").textContent = "Chỉnh sửa thông tin User";

	const passwordGroup = document.getElementById("passwordGroup");
	if (passwordGroup) {
		passwordGroup.style.display = "block";
		const passwordInput = document.getElementById("password");
		passwordInput.required = false;
		passwordInput.placeholder = "Nhập mật khẩu mới (để trống nếu không đổi)";
		const note = passwordGroup.querySelector(".form-note");
		if (note) note.textContent = "Để trống nếu không muốn thay đổi mật khẩu";
	}

	loadUserData(userId);

	showModal();
}

function loadUserData(userId) {
	showLoadingState();

	// Try to fetch user data from server first
	fetch(`/admin/users/get/${userId}`, {
		method: "GET",
		headers: {
			"X-Requested-With": "XMLHttpRequest",
			Accept: "application/json",
		},
	})
		.then((response) => response.json())
		.then((data) => {
			if (data.success && data.user) {
				populateFormFromUserData(data.user);
			} else {
				// Fallback to loading from DOM table
				loadUserDataFromDOM(userId);
			}
		})
		.catch((error) => {
			console.error(
				"Error loading user data from server, falling back to DOM:",
				error
			);
			// Fallback to loading from DOM table
			loadUserDataFromDOM(userId);
		})
		.finally(() => {
			hideLoadingState();
		});
}

function loadUserDataFromDOM(userId) {
	try {
		const allRows = document.querySelectorAll(".admin-table tbody tr");
		if (!allRows || allRows.length === 0) {
			showNotification("warning", "Không tìm thấy dữ liệu trong bảng.");
			return;
		}
		let userRow = null;

		for (let row of allRows) {
			const idCell = row.querySelector("td:first-child");
			if (idCell && idCell.textContent.trim() === userId.toString()) {
				userRow = row;
				break;
			}
		}

		if (userRow) {
			populateFormFromRow(userRow);
		} else {
			console.error("User row not found in DOM table");
			showNotification(
				"warning",
				"Không thể tải đầy đủ dữ liệu user. Một số thông tin có thể không hiển thị."
			);
			// Reset form but still allow editing
			resetForm();
			// Enable submit button so user can still make changes
			setTimeout(() => {
				toggleSubmitButton(true);
			}, 100);
		}
	} catch (error) {
		console.error("Error loading user data from DOM:", error);
		showNotification(
			"warning",
			"Có lỗi khi tải dữ liệu từ bảng. Vui lòng thử lại."
		);
		resetForm();
		setTimeout(() => {
			toggleSubmitButton(true);
		}, 100);
	}
}

function populateFormFromUserData(user) {
	document.getElementById("firstName").value = user.first_name || "";
	document.getElementById("lastName").value = user.last_name || "";
	document.getElementById("username").value = user.username || "";
	document.getElementById("email").value = user.email || "";
	document.getElementById("role").value = user.role || "user";
	document.getElementById("isActive").checked = user.is_active == 1;
	document.getElementById("rating").value = user.rating || -1;

	if (user.bio) {
		document.getElementById("bio").value = user.bio;
	}

	const socialFields = [
		"github_url",
		"linkedin_url",
		"website_url",
		"youtube_url",
		"facebook_url",
		"instagram_url",
	];
	socialFields.forEach((field) => {
		const element = document.getElementById(field);
		if (element && user[field]) {
			element.value = user[field];
		}
	});

	if (user.avatar) {
		const avatarPreview = document.getElementById("currentAvatarPreview");
		if (avatarPreview) {
			if (user.avatar.startsWith("data:image/")) {
				avatarPreview.src = user.avatar;
			} else {
				avatarPreview.src = `data:image/jpeg;base64,${user.avatar}`;
			}
		}
	}

	selectedBadges = [];
	document.querySelectorAll(".badge-item.selected").forEach((badge) => {
		badge.classList.remove("selected");
	});

	if (user.badges && Array.isArray(user.badges) && user.badges.length > 0) {
		selectedBadges = [...user.badges];

		setTimeout(() => {
			user.badges.forEach((badgeKey) => {
				const badgeElement = document.querySelector(`[data-badge="${badgeKey}"]`);
				if (badgeElement) {
					badgeElement.classList.add("selected");
				} else {
					console.warn("Badge element not found for:", badgeKey);
				}
			});
		}, 100);
	}

	saveOriginalFormData();
}

function populateFormFromRow(row) {
	try {
		const cells = row.querySelectorAll("td");

		const userId = cells[0]?.textContent.trim() || "";
		const userInfo = cells[1]?.querySelector(".user-details");
		const username = cells[2]?.textContent.replace("@", "").trim() || "";
		const email = cells[3]?.textContent.trim() || "";
		const role =
			cells[4]?.querySelector(".role-badge")?.textContent.toLowerCase().trim() ||
			"user";
		const isActive =
			cells[5]?.querySelector(".status-badge")?.classList.contains("status-active") ||
			false;
		const ratingText = cells[7]?.textContent.trim() || "Chưa xếp hạng";
		const rating = ratingText.includes("Chưa xếp hạng")
			? -1
			: parseInt(ratingText.replace(/,/g, "")) || -1;

		// Handle user name
		let firstName = "",
			lastName = "";
		if (userInfo) {
			const nameElement = userInfo.querySelector(".user-name");
			if (nameElement) {
				const names = nameElement.textContent.trim().split(" ");
				firstName = names[0] || "";
				lastName = names.slice(1).join(" ") || "";
			}
		}

		// Populate form fields
		document.getElementById("firstName").value = firstName;
		document.getElementById("lastName").value = lastName;
		document.getElementById("username").value = username;
		document.getElementById("email").value = email;
		document.getElementById("role").value = role;
		document.getElementById("isActive").checked = isActive;
		document.getElementById("rating").value = rating;

		// Handle avatar
		const avatarImg = cells[1]?.querySelector(".user-avatar .avatar-image");
		if (avatarImg && avatarImg.src) {
			document.getElementById("currentAvatarPreview").src = avatarImg.src;
		} else {
			document.getElementById("currentAvatarPreview").src =
				"/assets/default_avatar.png";
		}

		// Reset badges selection since we can't get this from DOM
		selectedBadges = [];
		document.querySelectorAll(".badge-item.selected").forEach((badge) => {
			badge.classList.remove("selected");
		});
	} catch (error) {
		console.error("Error populating form from row:", error);
		showNotification("warning", "Một số thông tin có thể không được tải đúng.");
	}

	saveOriginalFormData();
}

function resetForm() {
	const form = document.getElementById("userForm");
	if (form) {
		form.reset();

		if (!editingUserId) {
			selectedBadges = [];
			document.querySelectorAll(".badge-item.selected").forEach((badge) => {
				badge.classList.remove("selected");
			});
		}

		document.getElementById("currentAvatarPreview").src =
			"/assets/default_avatar.png";

		clearAllErrors();

		document.getElementById("role").value = "user";
		document.getElementById("isActive").checked = true;
		document.getElementById("rating").value = "-1";

		resetBadgesToggle();

		saveOriginalFormData();
		toggleSubmitButton(false);
	}
}

function saveOriginalFormData() {
	const form = document.getElementById("userForm");
	if (!form) return;

	originalFormData = {};

	const inputs = form.querySelectorAll("input, textarea, select");
	inputs.forEach((input) => {
		if (input.type === "checkbox") {
			originalFormData[input.id] = input.checked;
		} else if (input.type === "file") {
			return;
		} else {
			originalFormData[input.id] = input.value;
		}
	});

	originalFormData.selectedBadges = [...selectedBadges];
}

function checkFormChanges() {
	if (Object.keys(originalFormData).length === 0) {
		toggleSubmitButton(false);
		return;
	}

	const form = document.getElementById("userForm");
	if (!form) return;

	let hasChanges = false;

	const inputs = form.querySelectorAll("input, textarea, select");
	inputs.forEach((input) => {
		if (input.type === "file") {
			if (input.files && input.files.length > 0) {
				hasChanges = true;
			}
		} else if (input.type === "checkbox") {
			if (originalFormData[input.id] !== input.checked) {
				hasChanges = true;
			}
		} else {
			if (originalFormData[input.id] !== input.value) {
				hasChanges = true;
			}
		}
	});

	if (!arraysEqual(originalFormData.selectedBadges || [], selectedBadges)) {
		hasChanges = true;
	}

	toggleSubmitButton(hasChanges);
}

function arraysEqual(a, b) {
	if (a.length !== b.length) return false;
	const sortedA = [...a].sort();
	const sortedB = [...b].sort();
	return sortedA.every((val, index) => val === sortedB[index]);
}

function toggleSubmitButton(enable) {
	const submitBtn = document.querySelector(".auth-btn.signup-btn");
	if (submitBtn) {
		if (enable) {
			submitBtn.disabled = false;
			submitBtn.style.opacity = "1";
			submitBtn.style.cursor = "pointer";
		} else {
			submitBtn.disabled = true;
			submitBtn.style.opacity = "0.5";
			submitBtn.style.cursor = "not-allowed";
		}
	}
}

function resetBadgesToggle() {
	const toggleBtn = document.getElementById("badgesToggle");
	const hiddenBadges = document.querySelectorAll(".badge-item.badge-hidden");
	const toggleText = toggleBtn?.querySelector(".toggle-text");

	if (toggleBtn && toggleText) {
		hiddenBadges.forEach((badge) => {
			badge.style.display = "none";
		});

		toggleBtn.classList.remove("expanded");
		toggleText.textContent = "Show more";
	}
}

function toggleBadgeSelection(badgeKey, badgeElement) {
	const index = selectedBadges.indexOf(badgeKey);

	if (index > -1) {
		selectedBadges.splice(index, 1);
		badgeElement.classList.remove("selected");
	} else {
		selectedBadges.push(badgeKey);
		badgeElement.classList.add("selected");
	}

	checkFormChanges();
}

function handleFormSubmit(e) {
	e.preventDefault();

	const submitBtn = document.querySelector(".auth-btn.signup-btn");
	if (submitBtn && submitBtn.disabled) {
		return false;
	}

	if (!validateForm()) {
		return false;
	}

	const formData = new FormData();
	const form = e.target;

	const fields = [
		"firstName",
		"lastName",
		"username",
		"email",
		"bio",
		"rating",
		"github_url",
		"linkedin_url",
		"website_url",
		"youtube_url",
		"facebook_url",
		"instagram_url",
	];

	fields.forEach((field) => {
		const element = form.querySelector(`[name="${field}"]`);
		if (element && element.value) {
			formData.append(field, element.value);
		}
	});

	const passwordField = form.querySelector('[name="password"]');
	if (passwordField && passwordField.value) {
		formData.append("password", passwordField.value);
	}

	formData.append("role", form.querySelector('[name="role"]').value);

	const isActiveCheckbox = form.querySelector('[name="isActive"]');
	formData.append("isActive", isActiveCheckbox.checked ? "1" : "0");

	if (selectedBadges.length > 0) {
		formData.append("badges", JSON.stringify(selectedBadges));
	} else {
	}

	const avatarFile = form.querySelector('[name="avatar"]').files[0];
	if (avatarFile) {
		formData.append("avatar", avatarFile);
	}

	submitUserForm(formData);

	return false;
}

function submitUserForm(formData) {
	const submitBtn = document.querySelector(".auth-btn.signup-btn");
	const originalText = submitBtn.innerHTML;

	submitBtn.innerHTML = '<i class="bx bx-loader bx-spin"></i> Đang lưu...';
	submitBtn.disabled = true;

	let url, method;
	if (editingUserId) {
		url = `/admin/users/update/${editingUserId}`;
		method = "POST";
	} else {
		url = "/admin/users/create";
		method = "POST";
	}

	fetch(url, {
		method: method,
		body: formData,
	})
		.then((response) => response.json())
		.then((data) => {
			if (data.success) {
				showNotification("success", "User đã được tạo/cập nhật thành công!");
				closeEditModal();
				refreshUsersTable();
			} else {
				showNotification("error", data.message || "Có lỗi xảy ra khi xử lý yêu cầu");
			}
		})
		.catch((error) => {
			console.error("Error:", error);
			showNotification("error", "Có lỗi xảy ra khi xử lý yêu cầu");
		})
		.finally(() => {
			submitBtn.innerHTML = originalText;
			submitBtn.disabled = false;
		});
}

function validateForm() {
	let isValid = true;
	const requiredFields = ["firstName", "lastName", "username", "email"];

	clearAllErrors();

	requiredFields.forEach((fieldName) => {
		const field = document.getElementById(fieldName);
		if (!field.value.trim()) {
			showFieldError(field, "Trường này là bắt buộc");
			isValid = false;
		}
	});

	const emailField = document.getElementById("email");
	if (emailField.value && !isValidEmail(emailField.value)) {
		showFieldError(emailField, "Email không hợp lệ");
		isValid = false;
	}

	if (!editingUserId) {
		const passwordField = document.getElementById("password");
		if (!passwordField.value) {
			showFieldError(passwordField, "Mật khẩu là bắt buộc cho user mới");
			isValid = false;
		} else if (passwordField.value.length < 6) {
			showFieldError(passwordField, "Mật khẩu phải có ít nhất 6 ký tự");
			isValid = false;
		}
	}

	const urlFields = [
		"github_url",
		"linkedin_url",
		"website_url",
		"youtube_url",
		"facebook_url",
		"instagram_url",
	];
	urlFields.forEach((fieldName) => {
		const field = document.getElementById(fieldName);
		if (field.value && !isValidUrl(field.value)) {
			showFieldError(field, "URL không hợp lệ");
			isValid = false;
		}
	});

	return isValid;
}

function validateField(e) {
	const field = e.target;
	const fieldName = field.name || field.id;

	clearFieldError(field);

	if (field.hasAttribute("required") && !field.value.trim()) {
		showFieldError(field, "Trường này là bắt buộc");
		return false;
	}

	if (fieldName === "email" && field.value && !isValidEmail(field.value)) {
		showFieldError(field, "Email không hợp lệ");
		return false;
	}

	if (field.type === "url" && field.value && !isValidUrl(field.value)) {
		showFieldError(field, "URL không hợp lệ");
		return false;
	}

	return true;
}

function showFieldError(field, message) {
	const formGroup = field.closest(".form-group");
	if (formGroup) {
		formGroup.classList.add("error");

		let errorElement = formGroup.querySelector(".form-error");
		if (!errorElement) {
			errorElement = document.createElement("small");
			errorElement.className = "form-error";
			formGroup.appendChild(errorElement);
		}
		errorElement.textContent = message;
	}
}

function clearFieldError(fieldOrEvent) {
	// Handle both event object and direct field parameter
	const field = fieldOrEvent.target || fieldOrEvent;

	if (field && field.closest) {
		const formGroup = field.closest(".form-group");
		if (formGroup) {
			formGroup.classList.remove("error");
			const errorElement = formGroup.querySelector(".form-error");
			if (errorElement) {
				errorElement.remove();
			}
		}
	}
}

function clearAllErrors() {
	document.querySelectorAll(".form-group.error").forEach((group) => {
		group.classList.remove("error");
	});
	document.querySelectorAll(".form-error").forEach((error) => {
		error.remove();
	});
}

function isValidEmail(email) {
	const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
	return emailRegex.test(email);
}

function isValidUrl(url) {
	try {
		new URL(url);
		return true;
	} catch (e) {
		return false;
	}
}

function showModal() {
	const modal = document.getElementById("userModal");
	if (modal) {
		modal.classList.add("show");
		document.body.style.overflow = "hidden";

		setTimeout(() => {
			const firstInput = modal.querySelector('input:not([type="hidden"])');
			if (firstInput) {
				firstInput.focus();
			}

			// Re-setup badge selection when modal is shown
			setupBadgeSelection();

			// Reset badges toggle to initial state
			resetBadgesToggle();
		}, 300);
	}
}

function closeEditModal() {
	const modal = document.getElementById("userModal");
	if (modal) {
		modal.classList.remove("show");
		document.body.style.overflow = "";

		setTimeout(() => {
			resetForm();
		}, 300);
	}
}

function showLoadingState() {
	const form = document.getElementById("userForm");
	if (form) {
		form.style.opacity = "0.5";
		form.style.pointerEvents = "none";
	}
}

function hideLoadingState() {
	const form = document.getElementById("userForm");
	if (form) {
		form.style.opacity = "";
		form.style.pointerEvents = "";
	}
}

function previewAvatarInEdit(input) {
	if (input.files && input.files[0]) {
		const file = input.files[0];

		const allowedTypes = ["image/jpeg", "image/png", "image/gif", "image/webp"];
		const maxSize = 2 * 1024 * 1024;

		if (!allowedTypes.includes(file.type)) {
			showNotification("error", "Chỉ chấp nhận file JPG, PNG, GIF, WebP");
			input.value = "";
			return;
		}

		if (file.size > maxSize) {
			showNotification("error", "File quá lớn. Tối đa 2MB");
			input.value = "";
			return;
		}

		const reader = new FileReader();
		reader.onload = function (e) {
			document.getElementById("currentAvatarPreview").src = e.target.result;
		};
		reader.readAsDataURL(file);
	}
}

function openDeleteModal(userId, userName) {
	const deleteModal = document.getElementById("deleteModal");
	const deleteUserName = document.getElementById("deleteUserName");
	const confirmDeleteBtn = document.getElementById("confirmDelete");
	const cancelDeleteBtn = document.getElementById("cancelDelete");

	if (deleteUserName) {
		deleteUserName.textContent = userName;
	}

	// Remove any existing event listeners
	if (confirmDeleteBtn) {
		const newConfirmBtn = confirmDeleteBtn.cloneNode(true);
		confirmDeleteBtn.parentNode.replaceChild(newConfirmBtn, confirmDeleteBtn);
		newConfirmBtn.onclick = () => confirmDeleteUser(userId);
	}

	// Add cancel button event listener
	if (cancelDeleteBtn) {
		const newCancelBtn = cancelDeleteBtn.cloneNode(true);
		cancelDeleteBtn.parentNode.replaceChild(newCancelBtn, cancelDeleteBtn);
		newCancelBtn.onclick = closeDeleteModal;
	}

	if (deleteModal) {
		deleteModal.classList.add("show");
		document.body.style.overflow = "hidden";

		// Add event listeners for closing modal
		setupDeleteModalEvents(deleteModal);
	}
}

function setupDeleteModalEvents(modal) {
	// Close on backdrop click
	modal.addEventListener("click", function (e) {
		if (e.target === modal) {
			closeDeleteModal();
		}
	});

	// Close on escape key
	const escapeHandler = function (e) {
		if (e.key === "Escape") {
			closeDeleteModal();
			document.removeEventListener("keydown", escapeHandler);
		}
	};
	document.addEventListener("keydown", escapeHandler);

	// Close button
	const closeBtn = modal.querySelector(".delete-modal-close");
	if (closeBtn) {
		closeBtn.onclick = closeDeleteModal;
	}
}

function confirmDeleteUser(userId) {
	const confirmBtn = document.getElementById("confirmDelete");
	const originalText = confirmBtn.innerHTML;

	confirmBtn.innerHTML = '<i class="bx bx-loader bx-spin"></i> Đang xóa...';
	confirmBtn.disabled = true;

	fetch(`/admin/users/delete/${userId}`, {
		method: "DELETE",
		headers: {
			"Content-Type": "application/json",
		},
	})
		.then((response) => response.json())
		.then((data) => {
			if (data.success) {
				showNotification("success", "User đã được xóa thành công!");
				closeDeleteModal();
				// Refresh only the table instead of full page reload
				refreshUsersTable();
			} else {
				showNotification("error", data.message || "Có lỗi xảy ra khi xóa user");
			}
		})
		.catch((error) => {
			console.error("Error:", error);
			showNotification("error", "Có lỗi xảy ra khi xóa user");
		})
		.finally(() => {
			confirmBtn.innerHTML = originalText;
			confirmBtn.disabled = false;
		});
}

function closeDeleteModal() {
	const deleteModal = document.getElementById("deleteModal");
	if (deleteModal) {
		deleteModal.classList.remove("show");
		document.body.style.overflow = "";

		// Clean up event listeners
		const newModal = deleteModal.cloneNode(true);
		deleteModal.parentNode.replaceChild(newModal, deleteModal);
	}
}

function findTableCellByText(text) {
	const cells = document.querySelectorAll("td");
	for (let cell of cells) {
		if (cell.textContent.trim() === text.toString()) {
			return cell;
		}
	}
	return null;
}

async function refreshUsersTable() {
	try {
		const tableContainer = document.querySelector(".admin-table-container");
		if (!tableContainer) return;

		// Show loading state
		const originalContent = tableContainer.innerHTML;
		tableContainer.innerHTML = `
			<div class="loading-container" style="text-align: center; padding: 2rem;">
				<i class="bx bx-loader-alt bx-spin" style="font-size: 2rem; color: var(--primary-blue);"></i>
				<p style="margin-top: 1rem; color: var(--text-secondary);">Đang cập nhật dữ liệu...</p>
			</div>
		`;

		// Fetch fresh data
		const response = await fetch("/admin/users/table-data", {
			method: "GET",
			headers: {
				Accept: "application/json",
				"X-Requested-With": "XMLHttpRequest",
			},
		});

		if (!response.ok) {
			throw new Error("Failed to fetch table data");
		}

		const data = await response.json();

		if (data.success && data.html) {
			// Update table content
			tableContainer.innerHTML = data.html;

			// Re-initialize event listeners for new table content
			initializeTableEventListeners();
		} else {
			throw new Error("Invalid response data");
		}
	} catch (error) {
		console.error("Error refreshing table:", error);
		showNotification(
			"error",
			"Không thể cập nhật bảng dữ liệu. Vui lòng refresh trang."
		);

		// Fallback to page reload after a delay
		setTimeout(() => {
			window.location.reload();
		}, 2000);
	}
}

function initializeTableEventListeners() {
	// Re-setup edit button listeners
	const editButtons = document.querySelectorAll(".btn-edit");

	editButtons.forEach((btn) => {
		btn.addEventListener("click", function (e) {
			e.preventDefault();
			const userId =
				this.getAttribute("data-user-id") ||
				this.closest("tr").querySelector("td:first-child").textContent;
			openEditModal(userId);
		});
	});

	// Re-setup delete button listeners
	document.querySelectorAll(".btn-delete").forEach((btn) => {
		btn.addEventListener("click", function () {
			const userId =
				this.getAttribute("data-user-id") ||
				this.closest("tr").querySelector("td:first-child").textContent;
			const userName = this.closest("tr").querySelector(
				".user-details .user-name"
			).textContent;
			openDeleteModal(userId, userName);
		});
	});
}
