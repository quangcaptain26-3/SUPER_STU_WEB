// Hệ thống cập nhật thời gian thực và các thành phần live
class RealTimeSystem {
  // Hàm khởi tạo
  constructor() {
    this.initClock(); // Khởi tạo đồng hồ
    this.initLiveUpdates(); // Khởi tạo cập nhật live
    this.initNotifications(); // Khởi tạo hệ thống thông báo
  }

  // Khởi tạo đồng hồ thời gian thực
  initClock() {
    this.updateClock(); // Cập nhật lần đầu
    setInterval(() => this.updateClock(), 1000); // Cập nhật mỗi giây
  }

  // Cập nhật hiển thị đồng hồ
  updateClock() {
    const now = new Date(); // Lấy thời gian hiện tại
    const timeString = this.formatTime(now); // Định dạng chuỗi thời gian
    const dateString = this.formatDate(now); // Định dạng chuỗi ngày tháng

    // Cập nhật tất cả các element có class 'realtime-clock'
    document.querySelectorAll(".realtime-clock").forEach((element) => {
      element.textContent = timeString;
    });

    // Cập nhật tất cả các element có class 'realtime-date'
    document.querySelectorAll(".realtime-date").forEach((element) => {
      element.textContent = dateString;
    });

    // Cập nhật tất cả các element có class 'realtime-datetime'
    document.querySelectorAll(".realtime-datetime").forEach((element) => {
      element.textContent = `${dateString} ${timeString}`;
    });
  }

  // Định dạng thời gian sang chuỗi (HH:mm:ss)
  formatTime(date) {
    return date.toLocaleTimeString("vi-VN", {
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
      hour12: false, // Định dạng 24h
    });
  }

  // Định dạng ngày tháng sang chuỗi (Thứ, dd/mm/yyyy)
  formatDate(date) {
    return date.toLocaleDateString("vi-VN", {
      weekday: "long", // Tên thứ đầy đủ
      year: "numeric",
      month: "2-digit",
      day: "2-digit",
    });
  }

  // Khởi tạo các tác vụ cập nhật live
  initLiveUpdates() {
    // Cập nhật thống kê mỗi 30 giây
    setInterval(() => this.updateStatistics(), 30000);

    // Cập nhật danh sách sinh viên mỗi 60 giây
    setInterval(() => this.updateStudentList(), 60000);

    // Cập nhật danh sách điểm mỗi 60 giây
    setInterval(() => this.updateScoreList(), 60000);
  }

  // Cập nhật dữ liệu thống kê từ server
  async updateStatistics() {
    try {
      // Gọi API để lấy dữ liệu thống kê mới
      const response = await fetch("../charts/api/statistics.php");
      const data = await response.json();

      // Cập nhật các element trên giao diện nếu có dữ liệu mới
      if (data.total_students !== undefined) {
        this.updateElement("#totalStudents", data.total_students);
      }
      if (data.male_students !== undefined) {
        this.updateElement("#maleStudents", data.male_students);
      }
      if (data.female_students !== undefined) {
        this.updateElement("#femaleStudents", data.female_students);
      }
      if (data.avg_score !== undefined) {
        this.updateElement("#avgScore", data.avg_score);
      }

      // Hiển thị thông báo cập nhật thành công
      this.showUpdateNotification("Thống kê đã được cập nhật");
    } catch (error) {
      // Ghi lỗi ra console nếu có vấn đề
      console.log("Không thể cập nhật thống kê:", error);
    }
  }

  // Cập nhật danh sách sinh viên
  async updateStudentList() {
    // Chỉ cập nhật nếu đang ở trang danh sách sinh viên
    if (window.location.pathname.includes("students/list.php")) {
      try {
        // Gọi API để lấy danh sách sinh viên mới
        const response = await fetch("../students/api/get_students.php");
        const data = await response.json();

        if (data.success) {
          this.updateStudentTable(data.students); // Cập nhật bảng
          this.showUpdateNotification("Danh sách sinh viên đã được cập nhật");
        }
      } catch (error) {
        console.log("Không thể cập nhật danh sách sinh viên:", error);
      }
    }
  }

  // Cập nhật danh sách điểm
  async updateScoreList() {
    // Chỉ cập nhật nếu đang ở trang danh sách điểm
    if (window.location.pathname.includes("scores/list.php")) {
      try {
        // Gọi API để lấy danh sách điểm mới
        const response = await fetch("../scores/api/get_scores.php");
        const data = await response.json();

        if (data.success) {
          this.updateScoreTable(data.scores); // Cập nhật bảng
          this.showUpdateNotification("Danh sách điểm đã được cập nhật");
        }
      } catch (error) {
        console.log("Không thể cập nhật danh sách điểm:", error);
      }
    }
  }

  // Cập nhật nội dung của một element với hiệu ứng
  updateElement(selector, value) {
    const element = document.querySelector(selector);
    if (element) {
      // Thêm hiệu ứng fade out
      element.style.transition = "all 0.3s ease";
      element.style.opacity = "0.7";

      // Sau một khoảng thời gian ngắn, cập nhật nội dung và fade in
      setTimeout(() => {
        element.textContent = value;
        element.style.opacity = "1";
      }, 150);
    }
  }

  // Cập nhật lại toàn bộ bảng sinh viên
  updateStudentTable(students) {
    const tbody = document.querySelector("table tbody");
    if (!tbody) return; // Thoát nếu không tìm thấy tbody

    tbody.innerHTML = ""; // Xóa nội dung cũ của bảng

    // Lặp qua danh sách sinh viên và tạo lại các hàng
    students.forEach((student, index) => {
      const row = document.createElement("tr");
      row.innerHTML = `
                <td>${index + 1}</td>
                <td>
                    ${
                      student.avatar
                        ? `<img src="../uploads/avatars/${student.avatar}" class="avatar" alt="Avatar">`
                        : `<div class="avatar bg-secondary d-flex align-items-center justify-content-center">
                            <i class="fas fa-user text-white"></i>
                        </div>`
                    }
                </td>
                <td><strong>${student.msv}</strong></td>
                <td>${student.fullname}</td>
                <td>${this.formatDate(new Date(student.dob))}</td>
                <td>${this.getGenderText(student.gender)}</td>
                <td>${student.email}</td>
                <td>${student.phone}</td>
                <td>
                    <a href="edit.php?id=${
                      student.id
                    }" class="btn btn-sm btn-outline-primary btn-action" title="Sửa">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="view.php?id=${
                      student.id
                    }" class="btn btn-sm btn-outline-info btn-action" title="Xem">
                        <i class="fas fa-eye"></i>
                    </a>
                    <button onclick="deleteStudent(${
                      student.id
                    })" class="btn btn-sm btn-outline-danger btn-action" title="Xóa">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
      tbody.appendChild(row);
    });
  }

  // Cập nhật lại toàn bộ bảng điểm
  updateScoreTable(scores) {
    const tbody = document.querySelector("table tbody");
    if (!tbody) return; // Thoát nếu không tìm thấy tbody

    tbody.innerHTML = ""; // Xóa nội dung cũ

    // Lặp qua danh sách điểm và tạo lại các hàng
    scores.forEach((score, index) => {
      const row = document.createElement("tr");
      // Xác định màu sắc của badge dựa trên điểm
      const badgeClass =
        score.score >= 8
          ? "bg-success"
          : score.score >= 6
          ? "bg-warning"
          : "bg-danger";
      const grade = this.getGrade(score.score); // Lấy xếp loại

      row.innerHTML = `
                <td>${index + 1}</td>
                <td><strong>${score.msv}</strong></td>
                <td>${score.fullname}</td>
                <td>${score.subject}</td>
                <td><span class="badge ${badgeClass} score-badge">${
        score.score
      }</span></td>
                <td>${score.semester}</td>
                <td><span class="fw-bold">${grade}</span></td>
                <td>
                    <a href="edit.php?id=${
                      score.id
                    }" class="btn btn-sm btn-outline-primary btn-action" title="Sửa">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button onclick="deleteScore(${
                      score.id
                    })" class="btn btn-sm btn-outline-danger btn-action" title="Xóa">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
      tbody.appendChild(row);
    });
  }

  // Chuyển đổi mã giới tính sang dạng text
  getGenderText(gender) {
    const genderMap = {
      male: "Nam",
      female: "Nữ",
      other: "Khác",
    };
    return genderMap[gender] || "N/A";
  }

  // Lấy xếp loại học lực từ điểm số
  getGrade(score) {
    if (score >= 9) return "A+";
    if (score >= 8) return "A";
    if (score >= 7) return "B+";
    if (score >= 6) return "B";
    if (score >= 5) return "C";
    return "D";
  }

  // Khởi tạo container cho các thông báo
  initNotifications() {
    // Chỉ tạo nếu chưa tồn tại
    if (!document.querySelector(".notification-container")) {
      const container = document.createElement("div");
      container.className = "notification-container";
      container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                max-width: 300px;
            `;
      document.body.appendChild(container);
    }
  }

  // Hiển thị thông báo cập nhật live
  showUpdateNotification(message) {
    const container = document.querySelector(".notification-container");
    if (!container) return;

    const notification = document.createElement("div");
    notification.className = "alert alert-info alert-dismissible fade show";
    notification.style.cssText = `
            margin-bottom: 10px;
            animation: slideInRight 0.3s ease;
        `;

    notification.innerHTML = `
            <i class="fas fa-sync-alt me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

    container.appendChild(notification);

    // Tự động xóa thông báo sau 3 giây
    setTimeout(() => {
      if (notification.parentNode) {
        notification.remove();
      }
    }, 3000);
  }

  // Hiển thị thông báo thành công
  showSuccessNotification(message) {
    this.showNotification(message, "success");
  }

  // Hiển thị thông báo lỗi
  showErrorNotification(message) {
    this.showNotification(message, "danger");
  }

  // Hàm chung để hiển thị thông báo
  showNotification(message, type = "info") {
    const container = document.querySelector(".notification-container");
    if (!container) return;

    const notification = document.createElement("div");
    notification.className = `alert alert-${type} alert-dismissible fade show`;
    notification.style.cssText = `
            margin-bottom: 10px;
            animation: slideInRight 0.3s ease;
        `;
    
    // Chọn icon dựa trên loại thông báo
    const icon =
      type === "success"
        ? "check-circle"
        : type === "danger"
        ? "exclamation-triangle"
        : "info-circle";

    notification.innerHTML = `
            <i class="fas fa-${icon} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

    container.appendChild(notification);

    // Tự động xóa sau 5 giây
    setTimeout(() => {
      if (notification.parentNode) {
        notification.remove();
      }
    }, 5000);
  }
}

// Thêm CSS cho các animation và style cần thiết
const style = document.createElement("style");
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .realtime-clock {
        font-family: 'Courier New', monospace;
        font-weight: bold;
    }
    
    .live-indicator {
        display: inline-block;
        width: 8px;
        height: 8px;
        background-color: #28a745;
        border-radius: 50%;
        animation: pulse 2s infinite;
        margin-right: 5px;
    }
    
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
        }
    }
`;
document.head.appendChild(style);

// Khởi tạo hệ thống real-time khi DOM loaded
document.addEventListener("DOMContentLoaded", function () {
  window.realTimeSystem = new RealTimeSystem();
});

// Export cho sử dụng global
window.RealTimeSystem = RealTimeSystem;
