// Real-time Clock và Live Updates
class RealTimeSystem {
  constructor() {
    this.initClock();
    this.initLiveUpdates();
    this.initNotifications();
  }

  // Khởi tạo đồng hồ thời gian thực
  initClock() {
    this.updateClock();
    setInterval(() => this.updateClock(), 1000);
  }

  // Cập nhật đồng hồ
  updateClock() {
    const now = new Date();
    const timeString = this.formatTime(now);
    const dateString = this.formatDate(now);

    // Cập nhật tất cả các element có class realtime-clock
    document.querySelectorAll(".realtime-clock").forEach((element) => {
      element.textContent = timeString;
    });

    // Cập nhật tất cả các element có class realtime-date
    document.querySelectorAll(".realtime-date").forEach((element) => {
      element.textContent = dateString;
    });

    // Cập nhật datetime đầy đủ
    document.querySelectorAll(".realtime-datetime").forEach((element) => {
      element.textContent = `${dateString} ${timeString}`;
    });
  }

  // Format thời gian
  formatTime(date) {
    return date.toLocaleTimeString("vi-VN", {
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
      hour12: false,
    });
  }

  // Format ngày tháng
  formatDate(date) {
    return date.toLocaleDateString("vi-VN", {
      weekday: "long",
      year: "numeric",
      month: "2-digit",
      day: "2-digit",
    });
  }

  // Khởi tạo live updates
  initLiveUpdates() {
    // Cập nhật thống kê mỗi 30 giây
    setInterval(() => this.updateStatistics(), 30000);

    // Cập nhật danh sách sinh viên mỗi 60 giây
    setInterval(() => this.updateStudentList(), 60000);

    // Cập nhật danh sách điểm mỗi 60 giây
    setInterval(() => this.updateScoreList(), 60000);
  }

  // Cập nhật thống kê
  async updateStatistics() {
    try {
      const response = await fetch("../charts/api/statistics.php");
      const data = await response.json();

      // Cập nhật các số liệu thống kê
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

      // Hiển thị thông báo cập nhật
      this.showUpdateNotification("Thống kê đã được cập nhật");
    } catch (error) {
      console.log("Không thể cập nhật thống kê:", error);
    }
  }

  // Cập nhật danh sách sinh viên
  async updateStudentList() {
    // Chỉ cập nhật nếu đang ở trang danh sách sinh viên
    if (window.location.pathname.includes("students/list.php")) {
      try {
        const response = await fetch("../students/api/get_students.php");
        const data = await response.json();

        if (data.success) {
          this.updateStudentTable(data.students);
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
        const response = await fetch("../scores/api/get_scores.php");
        const data = await response.json();

        if (data.success) {
          this.updateScoreTable(data.scores);
          this.showUpdateNotification("Danh sách điểm đã được cập nhật");
        }
      } catch (error) {
        console.log("Không thể cập nhật danh sách điểm:", error);
      }
    }
  }

  // Cập nhật element
  updateElement(selector, value) {
    const element = document.querySelector(selector);
    if (element) {
      // Thêm hiệu ứng fade
      element.style.transition = "all 0.3s ease";
      element.style.opacity = "0.7";

      setTimeout(() => {
        element.textContent = value;
        element.style.opacity = "1";
      }, 150);
    }
  }

  // Cập nhật bảng sinh viên
  updateStudentTable(students) {
    const tbody = document.querySelector("table tbody");
    if (!tbody) return;

    tbody.innerHTML = "";

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

  // Cập nhật bảng điểm
  updateScoreTable(scores) {
    const tbody = document.querySelector("table tbody");
    if (!tbody) return;

    tbody.innerHTML = "";

    scores.forEach((score, index) => {
      const row = document.createElement("tr");
      const badgeClass =
        score.score >= 8
          ? "bg-success"
          : score.score >= 6
          ? "bg-warning"
          : "bg-danger";
      const grade = this.getGrade(score.score);

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

  // Lấy text giới tính
  getGenderText(gender) {
    const genderMap = {
      male: "Nam",
      female: "Nữ",
      other: "Khác",
    };
    return genderMap[gender] || "N/A";
  }

  // Lấy xếp loại điểm
  getGrade(score) {
    if (score >= 9) return "A+";
    if (score >= 8) return "A";
    if (score >= 7) return "B+";
    if (score >= 6) return "B";
    if (score >= 5) return "C";
    return "D";
  }

  // Khởi tạo thông báo
  initNotifications() {
    // Tạo container cho thông báo
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

  // Hiển thị thông báo
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

    // Tự động xóa sau 3 giây
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

  // Hiển thị thông báo
  showNotification(message, type = "info") {
    const container = document.querySelector(".notification-container");
    if (!container) return;

    const notification = document.createElement("div");
    notification.className = `alert alert-${type} alert-dismissible fade show`;
    notification.style.cssText = `
            margin-bottom: 10px;
            animation: slideInRight 0.3s ease;
        `;

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

    setTimeout(() => {
      if (notification.parentNode) {
        notification.remove();
      }
    }, 5000);
  }
}

// CSS cho animation
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
