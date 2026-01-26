/**
 * Notification System for SpecShop Admin
 * Hiển thị các thông báo toast/alert đẹp mắt
 */

class NotificationManager {
    constructor() {
        this.notifications = [];
        this.container = null;
        this.init();
    }

    init() {
        // Tạo container nếu chưa có
        if (!document.getElementById('notification-container')) {
            const container = document.createElement('div');
            container.id = 'notification-container';
            container.className = 'fixed top-6 right-6 z-[100] flex flex-col gap-4';
            document.body.appendChild(container);
            this.container = container;
        } else {
            this.container = document.getElementById('notification-container');
        }
    }

    /**
     * Hiển thị thông báo thành công
     * @param {string} message - Nội dung thông báo
     * @param {string} title - Tiêu đề (mặc định: "Success")
     */
    success(message, title = 'Success') {
        this.show({
            type: 'success',
            title: title,
            message: message,
            icon: 'check',
            bgColor: '#e6f4ea',
            borderColor: '#34a853',
            iconBgColor: '#34a853',
            textColor: '#0d652d'
        });
    }

    /**
     * Hiển thị thông báo lỗi
     * @param {string} message - Nội dung thông báo
     * @param {string} title - Tiêu đề (mặc định: "Error")
     */
    error(message, title = 'Error') {
        this.show({
            type: 'error',
            title: title,
            message: message,
            icon: 'error',
            bgColor: '#fef2f2',
            borderColor: '#ef4444',
            iconBgColor: '#ef4444',
            textColor: '#991b1b'
        });
    }

    /**
     * Hiển thị thông báo cảnh báo
     * @param {string} message - Nội dung thông báo
     * @param {string} title - Tiêu đề (mặc định: "Warning")
     */
    warning(message, title = 'Warning') {
        this.show({
            type: 'warning',
            title: title,
            message: message,
            icon: 'warning',
            bgColor: '#fffbeb',
            borderColor: '#f59e0b',
            iconBgColor: '#f59e0b',
            textColor: '#92400e'
        });
    }

    /**
     * Hiển thị thông báo thông tin
     * @param {string} message - Nội dung thông báo
     * @param {string} title - Tiêu đề (mặc định: "Info")
     */
    info(message, title = 'Info') {
        this.show({
            type: 'info',
            title: title,
            message: message,
            icon: 'info',
            bgColor: '#eff6ff',
            borderColor: '#3b82f6',
            iconBgColor: '#3b82f6',
            textColor: '#1e40af'
        });
    }

    /**
     * Hiển thị thông báo
     * @param {Object} options - Các tùy chọn cho thông báo
     */
    show(options) {
        const id = 'notification-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
        const notification = document.createElement('div');
        notification.id = id;
        notification.className = 'notification-item animate-in fade-in slide-in-from-top-4 duration-300';
        notification.style.minWidth = '320px';
        
        notification.innerHTML = `
            <div class="flex items-center gap-4 px-5 py-4 rounded-xl shadow-xl" 
                 style="background-color: ${options.bgColor}; border: 1px solid ${options.borderColor}20;">
                <div class="flex items-center justify-center rounded-full size-8 shrink-0 text-white" 
                     style="background-color: ${options.iconBgColor};">
                    <span class="material-symbols-outlined text-lg font-bold">${options.icon}</span>
                </div>
                <div class="flex flex-col grow">
                    <p class="text-sm font-semibold leading-none" style="color: ${options.textColor};">${this.escapeHtml(options.title)}</p>
                    <p class="text-sm font-medium mt-1" style="color: ${options.textColor};">${this.escapeHtml(options.message)}</p>
                </div>
                <button class="p-1 rounded-full transition-colors close-btn" 
                        style="color: ${options.textColor};" 
                        onclick="notificationManager.remove('${id}')"
                        onmouseover="this.style.backgroundColor='${options.iconBgColor}10'"
                        onmouseout="this.style.backgroundColor='transparent'">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>
        `;

        this.container.appendChild(notification);
        this.notifications.push({ id, element: notification });

        // Tự động xóa sau 5 giây
        setTimeout(() => {
            this.remove(id);
        }, 5000);
    }

    /**
     * Xóa thông báo
     * @param {string} id - ID của thông báo
     */
    remove(id) {
        const notification = document.getElementById(id);
        if (notification) {
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(-10px)';
            notification.style.transition = 'all 0.3s ease-out';
            
            setTimeout(() => {
                notification.remove();
                this.notifications = this.notifications.filter(n => n.id !== id);
            }, 300);
        }
    }

    /**
     * Escape HTML để tránh XSS
     * @param {string} text - Text cần escape
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Khởi tạo notification manager
const notificationManager = new NotificationManager();

// Export để sử dụng trong các file khác
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NotificationManager;
}
