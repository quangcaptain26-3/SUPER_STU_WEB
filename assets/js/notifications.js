// Hệ thống notification chuyên nghiệp
class NotificationSystem {
  constructor() {
    this.loadCSS();
    this.initToastContainer();
  }

  loadCSS() {
    // Load CSS if not already loaded
    if (!document.getElementById("notification-css")) {
      const link = document.createElement("link");
      link.id = "notification-css";
      link.rel = "stylesheet";
      link.href = "assets/css/notifications.css";
      document.head.appendChild(link);
    }
  }

  initToastContainer() {
    // Tạo container cho toast nếu chưa có
    if (!document.getElementById("toast-container")) {
      const container = document.createElement("div");
      container.id = "toast-container";
      container.className = "toast-container position-fixed top-0 end-0 p-3";
      container.style.zIndex = "9999";
      document.body.appendChild(container);
    }
  }

  // Hiển thị notification thành công
  success(message, title = "Thành công") {
    this.showToast("success", title, message, "fas fa-check-circle");
  }

  // Hiển thị notification lỗi
  error(message, title = "Lỗi") {
    this.showToast("danger", title, message, "fas fa-exclamation-circle");
  }

  // Hiển thị notification cảnh báo
  warning(message, title = "Cảnh báo") {
    this.showToast("warning", title, message, "fas fa-exclamation-triangle");
  }

  // Hiển thị notification thông tin
  info(message, title = "Thông tin") {
    this.showToast("info", title, message, "fas fa-info-circle");
  }

  // Tạo và hiển thị toast
  showToast(type, title, message, icon) {
    const toastId = "toast-" + Date.now();
    const toastHtml = `
            <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-${type} text-white">
                    <i class="${icon} me-2"></i>
                    <strong class="me-auto">${title}</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;

    document
      .getElementById("toast-container")
      .insertAdjacentHTML("beforeend", toastHtml);

    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
      autohide: true,
      delay: 5000,
    });

    toast.show();

    // Xóa toast khỏi DOM sau khi ẩn
    toastElement.addEventListener("hidden.bs.toast", () => {
      toastElement.remove();
    });
  }

  // Xác nhận xóa với SweetAlert2
  confirmDelete(
    title,
    text,
    confirmButtonText = "Xóa",
    cancelButtonText = "Hủy"
  ) {
    return Swal.fire({
      title: title,
      text: text,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#dc3545",
      cancelButtonColor: "#6c757d",
      confirmButtonText: `<i class="fas fa-trash me-2"></i>${confirmButtonText}`,
      cancelButtonText: `<i class="fas fa-times me-2"></i>${cancelButtonText}`,
      reverseButtons: true,
      focusCancel: true,
      customClass: {
        popup: "swal2-popup-custom",
        title: "swal2-title-custom",
        content: "swal2-content-custom",
        confirmButton: "swal2-confirm-custom",
        cancelButton: "swal2-cancel-custom",
      },
      buttonsStyling: false,
      showClass: {
        popup: "animate__animated animate__fadeInDown animate__faster",
      },
      hideClass: {
        popup: "animate__animated animate__fadeOutUp animate__faster",
      },
    });
  }

  // Xác nhận cập nhật
  confirmUpdate(
    title,
    text,
    confirmButtonText = "Cập nhật",
    cancelButtonText = "Hủy"
  ) {
    return Swal.fire({
      title: title,
      text: text,
      icon: "question",
      showCancelButton: true,
      confirmButtonColor: "#28a745",
      cancelButtonColor: "#6c757d",
      confirmButtonText: confirmButtonText,
      cancelButtonText: cancelButtonText,
      reverseButtons: true,
    });
  }

  // Xác nhận thêm mới
  confirmAdd(
    title,
    text,
    confirmButtonText = "Thêm",
    cancelButtonText = "Hủy"
  ) {
    return Swal.fire({
      title: title,
      text: text,
      icon: "info",
      showCancelButton: true,
      confirmButtonColor: "#17a2b8",
      cancelButtonColor: "#6c757d",
      confirmButtonText: confirmButtonText,
      cancelButtonText: cancelButtonText,
      reverseButtons: true,
    });
  }
}

// Khởi tạo hệ thống notification
const notification = new NotificationSystem();

// Hàm tiện ích để xác nhận xóa
function confirmDelete(title, text, callback) {
  notification.confirmDelete(title, text).then((result) => {
    if (result.isConfirmed) {
      // Hiển thị loading khi đang xử lý
      Swal.fire({
        title: "Đang xử lý...",
        text: "Vui lòng chờ trong giây lát",
        icon: "info",
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        },
      });
      callback();
    }
  });
}

// Hàm tiện ích để xác nhận cập nhật
function confirmUpdate(title, text, callback) {
  notification.confirmUpdate(title, text).then((result) => {
    if (result.isConfirmed) {
      callback();
    }
  });
}

// Hàm tiện ích để xác nhận thêm mới
function confirmAdd(title, text, callback) {
  notification.confirmAdd(title, text).then((result) => {
    if (result.isConfirmed) {
      callback();
    }
  });
}
