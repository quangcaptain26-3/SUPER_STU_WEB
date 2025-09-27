// Widget đồng hồ thời gian thực đẹp
class ClockWidget {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        this.init();
    }

    init() {
        this.createWidget();
        this.updateClock();
        setInterval(() => this.updateClock(), 1000);
    }

    createWidget() {
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

        // Thêm CSS
        const style = document.createElement('style');
        style.textContent = `
            .clock-widget {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 1rem;
                border-radius: 15px;
                text-align: center;
                box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                position: relative;
                overflow: hidden;
            }
            
            .clock-widget::before {
                content: '';
                position: absolute;
                top: -50%;
                left: -50%;
                width: 200%;
                height: 200%;
                background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
                animation: rotate 20s linear infinite;
            }
            
            @keyframes rotate {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            .clock-time {
                font-size: 2.5rem;
                font-weight: bold;
                font-family: 'Courier New', monospace;
                margin-bottom: 0.5rem;
                position: relative;
                z-index: 1;
            }
            
            .clock-date {
                font-size: 1rem;
                opacity: 0.9;
                margin-bottom: 0.5rem;
                position: relative;
                z-index: 1;
            }
            
            .clock-status {
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.8rem;
                position: relative;
                z-index: 1;
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
            
            .status-text {
                font-weight: 500;
            }
        `;
        document.head.appendChild(style);
    }

    updateClock() {
        const now = new Date();
        const timeString = this.formatTime(now);
        const dateString = this.formatDate(now);
        
        const timeElement = this.container.querySelector('.realtime-clock');
        const dateElement = this.container.querySelector('.realtime-date');
        
        if (timeElement) timeElement.textContent = timeString;
        if (dateElement) dateElement.textContent = dateString;
    }

    formatTime(date) {
        return date.toLocaleTimeString('vi-VN', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        });
    }

    formatDate(date) {
        return date.toLocaleDateString('vi-VN', {
            weekday: 'long',
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        });
    }
}

// Widget đồng hồ nhỏ cho sidebar
class MiniClockWidget {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        this.init();
    }

    init() {
        this.createWidget();
        this.updateClock();
        setInterval(() => this.updateClock(), 1000);
    }

    createWidget() {
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

        // Thêm CSS
        const style = document.createElement('style');
        style.textContent = `
            .mini-clock-widget {
                background: rgba(255,255,255,0.1);
                color: white;
                padding: 0.75rem;
                border-radius: 10px;
                text-align: center;
                margin: 1rem 0;
                border: 1px solid rgba(255,255,255,0.2);
            }
            
            .mini-clock-time {
                font-size: 1.1rem;
                font-weight: bold;
                font-family: 'Courier New', monospace;
                margin-bottom: 0.25rem;
            }
            
            .mini-clock-date {
                font-size: 0.8rem;
                opacity: 0.8;
            }
        `;
        document.head.appendChild(style);
    }

    updateClock() {
        const now = new Date();
        const timeString = this.formatTime(now);
        const dateString = this.formatDate(now);
        
        const timeElement = this.container.querySelector('.realtime-clock');
        const dateElement = this.container.querySelector('.realtime-date');
        
        if (timeElement) timeElement.textContent = timeString;
        if (dateElement) dateElement.textContent = dateString;
    }

    formatTime(date) {
        return date.toLocaleTimeString('vi-VN', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        });
    }

    formatDate(date) {
        return date.toLocaleDateString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }
}

// Export cho sử dụng global
window.ClockWidget = ClockWidget;
window.MiniClockWidget = MiniClockWidget;
