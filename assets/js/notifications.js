// Hệ thống notification chuyên nghiệp
class NotificationSystem {
  // Hàm khởi tạo
  constructor() {
    this.loadCSS(); // Tải file CSS cho notification
    this.initToastContainer(); // Khởi tạo container chứa các toast message
  }

  // Tải file CSS
  loadCSS() {
    // Chỉ tải nếu CSS chưa được tải
    if (!document.getElementById("notification-css")) {
      const link = document.createElement("link"); // Tạo element <link>
      link.id = "notification-css"; // Đặt ID để kiểm tra
      link.rel = "stylesheet"; // Loại liên kết là stylesheet
      link.href = "assets/css/notifications.css"; // Đường dẫn đến file CSS
      document.head.appendChild(link); // Thêm vào <head>
    }
  }

  // Khởi tạo container cho toast
  initToastContainer() {
    // Chỉ tạo nếu container chưa tồn tại
    if (!document.getElementById("toast-container")) {
      const container = document.createElement("div"); // Tạo element <div>
      container.id = "toast-container"; // Đặt ID
      container.className = "toast-container position-fixed top-0 end-0 p-3"; // Gán các class của Bootstrap
      container.style.zIndex = "9999"; // Đảm bảo container nằm trên cùng
      document.body.appendChild(container); // Thêm vào <body>
    }
  }

  // Hiển thị notification thành công
  success(message, title = "Thành công") {
    this.showToast("success", title, message, "fas fa-check-circle"); // Gọi hàm showToast với type 'success'
  }

  // Hiển thị notification lỗi
  error(message, title = "Lỗi") {
    this.showToast("danger", title, message, "fas fa-exclamation-circle"); // Gọi hàm showToast với type 'danger'
  }

  // Hiển thị notification cảnh báo
  warning(message, title = "Cảnh báo") {
    this.showToast("warning", title, message, "fas fa-exclamation-triangle"); // Gọi hàm showToast với type 'warning'
  }

  // Hiển thị notification thông tin
  info(message, title = "Thông tin") {
    this.showToast("info", title, message, "fas fa-info-circle"); // Gọi hàm showToast với type 'info'
  }

  // Hàm chính để tạo và hiển thị toast
  showToast(type, title, message, icon) {
    const toastId = "toast-" + Date.now(); // Tạo ID duy nhất cho mỗi toast
    // Tạo mã HTML cho toast
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

    // Chèn toast vào container
    document
      .getElementById("toast-container")
      .insertAdjacentHTML("beforeend", toastHtml);

    const toastElement = document.getElementById(toastId); // Lấy element toast vừa tạo
    // Khởi tạo toast của Bootstrap
    const toast = new bootstrap.Toast(toastElement, {
      autohide: true, // Tự động ẩn
      delay: 5000, // Sau 5 giây
    });

    toast.show(); // Hiển thị toast

    // Thêm sự kiện để xóa toast khỏi DOM sau khi đã ẩn
    toastElement.addEventListener("hidden.bs.toast", () => {
      toastElement.remove();
    });
  }

  // Hiển thị hộp thoại xác nhận xóa sử dụng SweetAlert2
  confirmDelete(
    title, // Tiêu đề hộp thoại
    text, // Nội dung hộp thoại
    confirmButtonText = "Xóa", // Text nút xác nhận
    cancelButtonText = "Hủy" // Text nút hủy
  ) {
    return Swal.fire({
      title: title,
      text: text,
      icon: "warning", // Icon cảnh báo
      showCancelButton: true, // Hiển thị nút hủy
      confirmButtonColor: "#dc3545", // Màu nút xác nhận (đỏ)
      cancelButtonColor: "#6c757d", // Màu nút hủy (xám)
      confirmButtonText: `<i class="fas fa-trash me-2"></i>${confirmButtonText}`, // Thêm icon vào nút xác nhận
      cancelButtonText: `<i class="fas fa-times me-2"></i>${cancelButtonText}`, // Thêm icon vào nút hủy
      reverseButtons: true, // Đảo ngược vị trí 2 nút
      focusCancel: true, // Focus vào nút hủy khi mở
      customClass: { // Tùy chỉnh class CSS cho các thành phần
        popup: "swal2-popup-custom",
        title: "swal2-title-custom",
        content: "swal2-content-custom",
        confirmButton: "swal2-confirm-custom",
        cancelButton: "swal2-cancel-custom",
      },
      buttonsStyling: false, // Không sử dụng style mặc định của SweetAlert2
      showClass: { // Hiệu ứng khi xuất hiện
        popup: "animate__animated animate__fadeInDown animate__faster",
      },
      hideClass: { // Hiệu ứng khi biến mất
        popup: "animate__animated animate__fadeOutUp animate__faster",
      },
    });
  }

  // Hiển thị hộp thoại xác nhận cập nhật
  confirmUpdate(
    title,
    text,
    confirmButtonText = "Cập nhật",
    cancelButtonText = "Hủy"
  ) {
    return Swal.fire({
      title: title,
      text: text,
      icon: "question", // Icon câu hỏi
      showCancelButton: true,
      confirmButtonColor: "#28a745", // Màu nút xác nhận (xanh)
      cancelButtonColor: "#6c757d",
      confirmButtonText: confirmButtonText,
      cancelButtonText: cancelButtonText,
      reverseButtons: true,
    });
  }

  // Hiển thị hộp thoại xác nhận thêm mới
  confirmAdd(
    title,
    text,
    confirmButtonText = "Thêm",
    cancelButtonText = "Hủy"
  ) {
    return Swal.fire({
      title: title,
      text: text,
      icon: "info", // Icon thông tin
      showCancelButton: true,
      confirmButtonColor: "#17a2b8", // Màu nút xác nhận (xanh lam)
      cancelButtonColor: "#6c757d",
      confirmButtonText: confirmButtonText,
      cancelButtonText: cancelButtonText,
      reverseButtons: true,
    });
  }
}

// Khởi tạo một đối tượng NotificationSystem để sử dụng toàn cục
const notification = new NotificationSystem();

// Hàm tiện ích để gọi hộp thoại xác nhận xóa
function confirmDelete(title, text, callback) {
  notification.confirmDelete(title, text).then((result) => {
    // Nếu người dùng nhấn nút xác nhận
    if (result.isConfirmed) {
      // Hiển thị thông báo loading trong khi chờ xử lý
      Swal.fire({
        title: "Đang xử lý...",
        text: "Vui lòng chờ trong giây lát",
        icon: "info",
        allowOutsideClick: false, // Không cho phép đóng khi click ra ngoài
        allowEscapeKey: false, // Không cho phép đóng bằng phím Esc
        showConfirmButton: false, // Ẩn nút OK
        didOpen: () => {
          Swal.showLoading(); // Hiển thị icon loading
        },
      });
      callback(); // Gọi hàm callback để thực hiện hành động xóa
    }
  });
}

// Hàm tiện ích để gọi hộp thoại xác nhận cập nhật
function confirmUpdate(title, text, callback) {
  notification.confirmUpdate(title, text).then((result) => {
    // Nếu người dùng nhấn nút xác nhận
    if (result.isConfirmed) {
      callback(); // Gọi hàm callback để thực hiện hành động cập nhật
    }
  });
}

// Hàm tiện ích để gọi hộp thoại xác nhận thêm mới
function confirmAdd(title, text, callback) {
  notification.confirmAdd(title, text).then((result) => {
    // Nếu người dùng nhấn nút xác nhận
    if (result.isConfirmed) {
      callback(); // Gọi hàm callback để thực hiện hành động thêm mới
    }
  });
}
