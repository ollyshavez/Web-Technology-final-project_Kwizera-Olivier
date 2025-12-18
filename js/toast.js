const Toast = {
    container: null,

    init() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.className = 'fixed bottom-4 right-4 z-50 flex flex-col gap-2';
            document.body.appendChild(this.container);
        }
    },

    show(message, type = 'success') {
        this.init();

        const colors = {
            success: 'bg-green-600 text-white',
            error: 'bg-red-600 text-white',
            info: 'bg-blue-600 text-white'
        };

        const icons = {
            success: '<i data-lucide="check-circle" width="18"></i>',
            error: '<i data-lucide="alert-circle" width="18"></i>',
            info: '<i data-lucide="info" width="18"></i>'
        };

        const toast = document.createElement('div');
        toast.className = `${colors[type]} px-4 py-3 rounded-lg shadow-lg flex items-center gap-3 transform transition-all duration-300 translate-y-2 opacity-0 min-w-[300px]`;
        toast.innerHTML = `
            ${icons[type]}
            <span class="font-medium text-sm">${message}</span>
        `;

        this.container.appendChild(toast);

        // Refresh icons if lucide exists
        if (window.lucide) window.lucide.createIcons();

        // Animate In
        requestAnimationFrame(() => {
            toast.classList.remove('translate-y-2', 'opacity-0');
        });

        // Remove after 3s
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-x-full');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
};

window.showToast = (msg, type) => Toast.show(msg, type);
