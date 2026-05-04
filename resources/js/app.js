/**
 * app.js - Main Application Entry Point
 * -----------------------------------------------------------
 * Initializes the Vue 3 + Inertia.js application.
 * Registers plugins, global components, and mounts the app.
 */

// Bootstrap Axios and other libraries
import "./bootstrap";

// Import Tailwind CSS
import "../css/app.css";

// Vue 3 core
import { createApp, h } from "vue";

// Inertia.js for server-side rendering bridge
import { createInertiaApp } from "@inertiajs/vue3";

// Laravel Vite plugin for page resolution
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";

// Ziggy for route() helper in Vue
import { ZiggyVue } from "../../vendor/tightenco/ziggy/dist/index.js";

// Font Awesome icons
import "@fortawesome/fontawesome-free/css/all.min.css";

// App name from environment
const appName = import.meta.env.VITE_APP_NAME || "Laravel";

/**
 * Global Toast Function
 * Shows a toast notification in the bottom-right corner
 * Usage: window.showToast('Message', 'success')
 */
window.showToast = function (message, type = "success") {
    // Remove existing toast if any
    const existingToast = document.querySelector(".global-toast");
    if (existingToast) existingToast.remove();

    // Create toast element
    const toast = document.createElement("div");
    toast.className = `global-toast fixed bottom-4 sm:bottom-6 right-4 sm:right-6 z-[9999] flex items-center gap-2 sm:gap-3 px-4 sm:px-5 py-2.5 sm:py-3 rounded-xl shadow-2xl animate-slide-in-right`;

    // Set colors based on type
    const colors = {
        success: "bg-emerald-500 text-white",
        error: "bg-red-500 text-white",
        warning: "bg-amber-500 text-white",
        info: "bg-blue-500 text-white",
    };
    toast.className += ` ${colors[type] || colors.success}`;

    // Set icon based on type
    const icons = {
        success:
            '<i class="fa-solid fa-circle-check text-base sm:text-lg"></i>',
        error: '<i class="fa-solid fa-circle-exclamation text-base sm:text-lg"></i>',
        warning:
            '<i class="fa-solid fa-triangle-exclamation text-base sm:text-lg"></i>',
        info: '<i class="fa-solid fa-circle-info text-base sm:text-lg"></i>',
    };

    toast.innerHTML = `
        ${icons[type] || icons.success}
        <span class="text-xs sm:text-sm font-medium">${message}</span>
        <button class="ml-1 sm:ml-2 hover:opacity-70 transition-opacity">
            <i class="fa-solid fa-xmark text-xs sm:text-sm"></i>
        </button>
    `;

    document.body.appendChild(toast);

    // Add click handler to close button
    const closeBtn = toast.querySelector("button");
    closeBtn.addEventListener("click", () => toast.remove());

    // Auto remove after 4 seconds
    setTimeout(() => {
        if (toast && toast.parentNode) {
            toast.classList.add("animate-fade-out");
            setTimeout(() => {
                if (toast && toast.parentNode) toast.remove();
            }, 300);
        }
    }, 4000);
};

// Add global styles for toast animations
const style = document.createElement("style");
style.textContent = `
    @keyframes slide-in-right {
        from { opacity: 0; transform: translateX(100px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes fade-out {
        from { opacity: 1; transform: translateX(0); }
        to { opacity: 0; transform: translateX(100px); }
    }
    .animate-slide-in-right { animation: slide-in-right 0.3s ease-out forwards; }
    .animate-fade-out { animation: fade-out 0.3s ease-out forwards; }
    
    /* Compact mode */
    .compact-mode .p-6 { padding: 1rem !important; }
    .compact-mode .gap-6 { gap: 1rem !important; }
    .compact-mode .mb-8 { margin-bottom: 1rem !important; }
    
    /* Reduce motion for accessibility */
    .reduce-motion *, .reduce-motion *::before, .reduce-motion *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
    }
`;
document.head.appendChild(style);

/**
 * Create Inertia App
 * Resolves Vue pages from the Pages directory
 */
createInertiaApp({
    // Dynamic page title
    title: (title) => `${title} - ${appName}`,

    // Resolve page components from Pages directory
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue"),
        ),

    // Setup Vue app with plugins
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin) // Inertia plugin
            .use(ZiggyVue) // Ziggy for route() helper
            .mount(el); // Mount to DOM
    },
});
