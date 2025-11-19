// Widget đồng hồ thời gian thực đẹp
class ClockWidget {
    // Hàm khởi tạo, nhận vào id của container
    constructor(containerId) {
        this.container = document.getElementById(containerId); // Lấy element container
        this.init(); // Gọi hàm khởi tạo
    }

    // Hàm khởi tạo chính
    init() {
        this.createWidget(); // Tạo cấu trúc HTML và CSS cho widget
        this.updateClock(); // Cập nhật đồng hồ lần đầu
        setInterval(() => this.updateClock(), 1000); // Cập nhật đồng hồ mỗi giây
    }

    // Tạo cấu trúc HTML và CSS
    createWidget() {
        // Chèn mã HTML của widget vào container
        this.container.innerHTML = `
            <div class="clock-widget">
                <div class="clock-time">
                    <span class="realtime-clock">--:--:--</span>
                </div>
                <div class="clock-date">
                    <span class="realtime-date">--/--/----</span>
                </div>
                <div class="clock-status">
                    <span class="live-indicator"></span>
                    <span class="status-text">Live</span>
                </div>
            </div>
        `;

        // Thêm CSS để tạo kiểu cho widget
        const style = document.createElement('style'); // Tạo một element <style>
        style.textContent = `
            .clock-widget {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); /* Nền gradient */
                color: white; /* Chữ màu trắng */
                padding: 1rem; /* Đệm */
                border-radius: 15px; /* Bo góc */
                text-align: center; /* Căn giữa nội dung */
                box-shadow: 0 4px 15px rgba(0,0,0,0.2); /* Đổ bóng */
                position: relative; /* Vị trí tương đối */
                overflow: hidden; /* Ẩn các phần tử tràn ra ngoài */
            }
            
            .clock-widget::before {
                content: ''; /* Nội dung rỗng */
                position: absolute; /* Vị trí tuyệt đối */
                top: -50%;
                left: -50%;
                width: 200%;
                height: 200%;
                background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); /* Hiệu ứng ánh sáng */
                animation: rotate 20s linear infinite; /* Animation xoay */
            }
            
            @keyframes rotate {
                0% { transform: rotate(0deg); } /* Bắt đầu xoay */
                100% { transform: rotate(360deg); } /* Kết thúc xoay */
            }
            
            .clock-time {
                font-size: 2.5rem; /* Cỡ chữ lớn cho thời gian */
                font-weight: bold; /* Chữ đậm */
                font-family: 'Courier New', monospace; /* Font chữ */
                margin-bottom: 0.5rem; /* Khoảng cách dưới */
                position: relative; /* Vị trí tương đối */
                z-index: 1; /* Đặt lên trên hiệu ứng nền */
            }
            
            .clock-date {
                font-size: 1rem; /* Cỡ chữ cho ngày tháng */
                opacity: 0.9; /* Độ mờ */
                margin-bottom: 0.5rem; /* Khoảng cách dưới */
                position: relative; /* Vị trí tương đối */
                z-index: 1; /* Đặt lên trên hiệu ứng nền */
            }
            
            .clock-status {
                display: flex; /* Hiển thị dạng flex */
                align-items: center; /* Căn giữa theo chiều dọc */
                justify-content: center; /* Căn giữa theo chiều ngang */
                font-size: 0.8rem; /* Cỡ chữ */
                position: relative; /* Vị trí tương đối */
                z-index: 1; /* Đặt lên trên hiệu ứng nền */
            }
            
            .live-indicator {
                display: inline-block; /* Hiển thị trên cùng một dòng */
                width: 8px; /* Chiều rộng */
                height: 8px; /* Chiều cao */
                background-color: #28a745; /* Màu nền xanh */
                border-radius: 50%; /* Bo tròn thành hình tròn */
                animation: pulse 2s infinite; /* Animation nhấp nháy */
                margin-right: 5px; /* Khoảng cách phải */
            }
            
            @keyframes pulse {
                0% {
                    box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); /* Bắt đầu hiệu ứng */
                }
                70% {
                    box-shadow: 0 0 0 10px rgba(40, 167, 69, 0); /* Hiệu ứng tỏa ra */
                }
                100% {
                    box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); /* Kết thúc hiệu ứng */
                }
            }
            
            .status-text {
                font-weight: 500; /* Độ đậm của chữ */
            }
        `;
        document.head.appendChild(style); // Thêm element <style> vào <head>
    }

    // Cập nhật hiển thị đồng hồ
    updateClock() {
        const now = new Date(); // Lấy thời gian hiện tại
        const timeString = this.formatTime(now); // Định dạng chuỗi thời gian
        const dateString = this.formatDate(now); // Định dạng chuỗi ngày tháng
        
        const timeElement = this.container.querySelector('.realtime-clock'); // Lấy element hiển thị thời gian
        const dateElement = this.container.querySelector('.realtime-date'); // Lấy element hiển thị ngày tháng
        
        if (timeElement) timeElement.textContent = timeString; // Cập nhật thời gian nếu element tồn tại
        if (dateElement) dateElement.textContent = dateString; // Cập nhật ngày tháng nếu element tồn tại
    }

    // Định dạng thời gian sang chuỗi (HH:mm:ss)
    formatTime(date) {
        return date.toLocaleTimeString('vi-VN', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false // Sử dụng định dạng 24h
        });
    }

    // Định dạng ngày tháng sang chuỗi (Thứ, dd/mm/yyyy)
    formatDate(date) {
        return date.toLocaleDateString('vi-VN', {
            weekday: 'long', // Hiển thị tên thứ đầy đủ
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        });
    }
}

// Widget đồng hồ nhỏ cho sidebar
class MiniClockWidget {
    // Hàm khởi tạo, nhận vào id của container
    constructor(containerId) {
        this.container = document.getElementById(containerId); // Lấy element container
        this.init(); // Gọi hàm khởi tạo
    }

    // Hàm khởi tạo chính
    init() {
        this.createWidget(); // Tạo cấu trúc HTML và CSS cho widget
        this.updateClock(); // Cập nhật đồng hồ lần đầu
        setInterval(() => this.updateClock(), 1000); // Cập nhật đồng hồ mỗi giây
    }

    // Tạo cấu trúc HTML và CSS
    createWidget() {
        // Chèn mã HTML của widget vào container
        this.container.innerHTML = `
            <div class="mini-clock-widget">
                <div class="mini-clock-time">
                    <i class="fas fa-clock me-2"></i>
                    <span class="realtime-clock">--:--:--</span>
                </div>
                <div class="mini-clock-date">
                    <span class="realtime-date">--/--/----</span>
                </div>
            </div>
        `;

        // Thêm CSS để tạo kiểu cho widget
        const style = document.createElement('style'); // Tạo một element <style>
        style.textContent = `
            .mini-clock-widget {
                background: rgba(255,255,255,0.1); /* Nền mờ */
                color: white; /* Chữ màu trắng */
                padding: 0.75rem; /* Đệm */
                border-radius: 10px; /* Bo góc */
                text-align: center; /* Căn giữa nội dung */
                margin: 1rem 0; /* Khoảng cách trên dưới */
                border: 1px solid rgba(255,255,255,0.2); /* Viền mờ */
            }
            
            .mini-clock-time {
                font-size: 1.1rem; /* Cỡ chữ thời gian */
                font-weight: bold; /* Chữ đậm */
                font-family: 'Courier New', monospace; /* Font chữ */
                margin-bottom: 0.25rem; /* Khoảng cách dưới */
            }
            
            .mini-clock-date {
                font-size: 0.8rem; /* Cỡ chữ ngày tháng */
                opacity: 0.8; /* Độ mờ */
            }
        `;
        document.head.appendChild(style); // Thêm element <style> vào <head>
    }

    // Cập nhật hiển thị đồng hồ
    updateClock() {
        const now = new Date(); // Lấy thời gian hiện tại
        const timeString = this.formatTime(now); // Định dạng chuỗi thời gian
        const dateString = this.formatDate(now); // Định dạng chuỗi ngày tháng
        
        const timeElement = this.container.querySelector('.realtime-clock'); // Lấy element hiển thị thời gian
        const dateElement = this.container.querySelector('.realtime-date'); // Lấy element hiển thị ngày tháng
        
        if (timeElement) timeElement.textContent = timeString; // Cập nhật thời gian nếu element tồn tại
        if (dateElement) dateElement.textContent = dateString; // Cập nhật ngày tháng nếu element tồn tại
    }

    // Định dạng thời gian sang chuỗi (HH:mm:ss)
    formatTime(date) {
        return date.toLocaleTimeString('vi-VN', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false // Sử dụng định dạng 24h
        });
    }

    // Định dạng ngày tháng sang chuỗi (dd/mm/yyyy)
    formatDate(date) {
        return date.toLocaleDateString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }
}

// Export cho sử dụng global, để có thể gọi từ bất kỳ đâu trong ứng dụng
window.ClockWidget = ClockWidget;
window.MiniClockWidget = MiniClockWidget;
