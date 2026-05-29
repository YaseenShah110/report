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
import "./showAlert.js";
// App name from environment
const appName = import.meta.env.VITE_APP_NAME || "Laravel";
import Swal from "sweetalert2";
/**
 * Global Toast Function
 * Shows a toast notification in the bottom-right corner
 * Usage: window.showToast('Message', 'success')
 */
window.showToast = function (message, type) {
    type = ["success", "error", "warning", "info"].includes(type)
        ? type
        : "success";

    const cfg = {
        success: {
            bg: "#059669",
            border: "#34d399",
            icon: "✓",
            label: "Success",
        },
        error: { bg: "#dc2626", border: "#f87171", icon: "✕", label: "Error" },
        warning: {
            bg: "#d97706",
            border: "#fcd34d",
            icon: "⚠",
            label: "Warning",
        },
        info: { bg: "#4f46e5", border: "#818cf8", icon: "ℹ", label: "Info" },
    };
    const c = cfg[type];

    // ── Container (persists across toasts) ────────────────────────────
    let stack = document.getElementById("__toast_stack__");
    if (!stack) {
        stack = document.createElement("div");
        stack.id = "__toast_stack__";
        Object.assign(stack.style, {
            position: "fixed",
            top: "1.25rem",
            right: "1.25rem",
            zIndex: "99999",
            display: "flex",
            flexDirection: "column",
            gap: "0.5rem",
            pointerEvents: "none",
            width: "320px",
            maxWidth: "calc(100vw - 2rem)",
        });
        document.body.appendChild(stack);
    }

    // ── Toast element ─────────────────────────────────────────────────
    const toast = document.createElement("div");
    Object.assign(toast.style, {
        pointerEvents: "all",
        display: "flex",
        alignItems: "flex-start",
        gap: "0.625rem",
        padding: "0.75rem 1rem",
        borderRadius: "0.875rem",
        background: "#ffffff",
        border: `1px solid ${c.border}`,
        borderLeft: `4px solid ${c.bg}`,
        boxShadow: "0 8px 32px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.06)",
        fontFamily: "inherit",
        cursor: "pointer",
        position: "relative",
        overflow: "hidden",
        transform: "translateX(calc(100% + 2rem))",
        opacity: "0",
        transition:
            "transform 0.4s cubic-bezier(0.34,1.56,0.64,1), opacity 0.3s ease",
        maxHeight: "120px",
    });

    // Dark mode: check html.dark class
    const isDark = document.documentElement.classList.contains("dark");
    if (isDark) {
        toast.style.background = "#1e293b";
        toast.style.border = `1px solid ${c.border}33`;
        toast.style.borderLeft = `4px solid ${c.bg}`;
    }

    // Icon circle
    const iconEl = document.createElement("div");
    Object.assign(iconEl.style, {
        flexShrink: "0",
        width: "28px",
        height: "28px",
        borderRadius: "50%",
        background: c.bg + "18",
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        fontSize: "13px",
        fontWeight: "700",
        color: c.bg,
        marginTop: "1px",
    });
    iconEl.textContent = c.icon;

    // Text block
    const textEl = document.createElement("div");
    textEl.style.flex = "1";
    textEl.style.minWidth = "0";

    const titleEl = document.createElement("div");
    Object.assign(titleEl.style, {
        fontSize: "0.8125rem",
        fontWeight: "700",
        color: isDark ? "#f1f5f9" : "#0f172a",
        lineHeight: "1.3",
        marginBottom: "2px",
    });
    titleEl.textContent = c.label;

    const msgEl = document.createElement("div");
    Object.assign(msgEl.style, {
        fontSize: "0.8125rem",
        color: isDark ? "#94a3b8" : "#475569",
        lineHeight: "1.5",
        wordBreak: "break-word",
    });
    msgEl.textContent = message;

    textEl.appendChild(titleEl);
    textEl.appendChild(msgEl);

    // Close button
    const closeEl = document.createElement("button");
    Object.assign(closeEl.style, {
        flexShrink: "0",
        width: "20px",
        height: "20px",
        borderRadius: "4px",
        border: "none",
        background: "transparent",
        color: isDark ? "#64748b" : "#94a3b8",
        cursor: "pointer",
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        fontSize: "14px",
        lineHeight: "1",
        padding: "0",
    });
    closeEl.innerHTML = "&times;";
    closeEl.setAttribute("aria-label", "Dismiss");

    // Progress bar
    const progressWrap = document.createElement("div");
    Object.assign(progressWrap.style, {
        position: "absolute",
        bottom: "0",
        left: "0",
        right: "0",
        height: "3px",
        background: isDark ? "rgba(255,255,255,0.06)" : "rgba(0,0,0,0.06)",
        borderRadius: "0 0 0.875rem 0.875rem",
    });
    const progressBar = document.createElement("div");
    Object.assign(progressBar.style, {
        height: "100%",
        width: "100%",
        background: c.bg,
        transformOrigin: "left",
        borderRadius: "inherit",
    });
    progressWrap.appendChild(progressBar);

    toast.appendChild(iconEl);
    toast.appendChild(textEl);
    toast.appendChild(closeEl);
    toast.appendChild(progressWrap);
    stack.appendChild(toast);

    // ── Animate IN (two rAF to ensure transition fires) ───────────────
    requestAnimationFrame(() =>
        requestAnimationFrame(() => {
            toast.style.transform = "translateX(0)";
            toast.style.opacity = "1";
        }),
    );

    // ── Progress bar shrink ───────────────────────────────────────────
    const DURATION = 4000;
    setTimeout(() => {
        progressBar.style.transition = `transform ${DURATION}ms linear`;
        progressBar.style.transform = "scaleX(0)";
    }, 60);

    // ── Dismiss function ──────────────────────────────────────────────
    let timer;
    function dismiss() {
        clearTimeout(timer);
        toast.style.transition =
            "transform 0.3s ease, opacity 0.25s ease, max-height 0.3s ease 0.05s, margin 0.3s ease, padding 0.3s ease";
        toast.style.transform = "translateX(calc(100% + 2rem))";
        toast.style.opacity = "0";
        toast.style.maxHeight = "0";
        toast.style.margin = "0";
        toast.style.padding = "0";
        setTimeout(() => {
            if (toast.parentNode) toast.parentNode.removeChild(toast);
            if (stack && stack.children.length === 0 && stack.parentNode) {
                stack.parentNode.removeChild(stack);
                // remove reference so next call recreates it
                const el = document.getElementById("__toast_stack__");
                if (!el) {
                    /* already gone */
                }
            }
        }, 350);
    }

    timer = setTimeout(dismiss, DURATION + 60);

    // Click anywhere on toast or × to dismiss
    toast.addEventListener("click", dismiss);
    closeEl.addEventListener("click", function (e) {
        e.stopPropagation();
        dismiss();
    });

    // Hover to pause
    let pausedAt = 0,
        remaining = DURATION;
    toast.addEventListener("mouseenter", function () {
        clearTimeout(timer);
        pausedAt = Date.now();
        progressBar.style.transition = "none";
    });
    toast.addEventListener("mouseleave", function () {
        if (!pausedAt) return;
        remaining -= Date.now() - pausedAt;
        pausedAt = 0;
        if (remaining <= 0) {
            dismiss();
            return;
        }
        progressBar.style.transition = `transform ${remaining}ms linear`;
        progressBar.style.transform = "scaleX(0)";
        timer = setTimeout(dismiss, remaining);
    });
};

// window.showToast = function (message, type = "success") {
//     // Remove existing toast if any
//     const existingToast = document.querySelector(".global-toast");
//     if (existingToast) existingToast.remove();

//     // Create toast element
//     const toast = document.createElement("div");
//     toast.className = `global-toast fixed bottom-4 sm:bottom-6 right-4 sm:right-6 z-[9999] flex items-center gap-2 sm:gap-3 px-4 sm:px-5 py-2.5 sm:py-3 rounded-xl shadow-2xl animate-slide-in-right`;

//     // Set colors based on type
//     const colors = {
//         success: "bg-emerald-500 text-white",
//         error: "bg-red-500 text-white",
//         warning: "bg-amber-500 text-white",
//         info: "bg-blue-500 text-white",
//     };
//     toast.className += ` ${colors[type] || colors.success}`;

//     // Set icon based on type
//     const icons = {
//         success:
//             '<i class="fa-solid fa-circle-check text-base sm:text-lg"></i>',
//         error: '<i class="fa-solid fa-circle-exclamation text-base sm:text-lg"></i>',
//         warning:
//             '<i class="fa-solid fa-triangle-exclamation text-base sm:text-lg"></i>',
//         info: '<i class="fa-solid fa-circle-info text-base sm:text-lg"></i>',
//     };

//     toast.innerHTML = `
//         ${icons[type] || icons.success}
//         <span class="text-xs sm:text-sm font-medium">${message}</span>
//         <button class="ml-1 sm:ml-2 hover:opacity-70 transition-opacity">
//             <i class="fa-solid fa-xmark text-xs sm:text-sm"></i>
//         </button>
//     `;

//     document.body.appendChild(toast);

//     // Add click handler to close button
//     const closeBtn = toast.querySelector("button");
//     closeBtn.addEventListener("click", () => toast.remove());

//     // Auto remove after 4 seconds
//     setTimeout(() => {
//         if (toast && toast.parentNode) {
//             toast.classList.add("animate-fade-out");
//             setTimeout(() => {
//                 if (toast && toast.parentNode) toast.remove();
//             }, 300);
//         }
//     }, 4000);
// };

// window.showToast = (message, type = 'success') => {
//     const icons = {
//         success: '✅',
//         error:   '❌',
//         warning: '⚠️',
//         info:    'ℹ️',
//     };
 
//     const colors = {
//         success: { bg: '#f0fdf4', border: '#bbf7d0', text: '#15803d', icon: '#22c55e' },
//         error:   { bg: '#fef2f2', border: '#fecaca', text: '#dc2626', icon: '#ef4444' },
//         warning: { bg: '#fffbeb', border: '#fde68a', text: '#d97706', icon: '#f59e0b' },
//         info:    { bg: '#eff6ff', border: '#bfdbfe', text: '#2563eb', icon: '#3b82f6' },
//     };
 
//     const c = colors[type] || colors.info;
 
//     Swal.fire({
//         toast:             true,
//         position:          'top-end',
//         icon:              type,
//         title:             message,
//         showConfirmButton: false,
//         timer:             1000,
//         timerProgressBar:  true,
//         showClass:  { popup: 'animate__animated animate__slideInRight animate__faster' },
//         hideClass:  { popup: 'animate__animated animate__slideOutRight animate__faster' },
//         customClass: {
//             popup:        'rounded-2xl border shadow-xl text-sm font-semibold',
//             timerProgressBar: 'rounded-full',
//         },
//         background:  c.bg,
//         color:       c.text,
//         didOpen: toast => {
//             toast.style.border        = `1px solid ${c.border}`;
//             toast.style.boxShadow     = `0 10px 40px ${c.icon}25`;
//             toast.style.fontFamily    = 'inherit';
//             const bar = toast.querySelector('.swal2-timer-progress-bar');
//             if (bar) bar.style.background = c.icon;
//         },
//     });
// };

window.showConfirm = ({
    title = "Are you sure?",
    text = "",
    icon = "warning",
    confirmText = "Confirm",
    cancelText = "Cancel",
    confirmColor = "#7c3aed",
} = {}) => {
    return Swal.fire({
        title,
        text,
        icon,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        confirmButtonColor: confirmColor,
        cancelButtonColor: "#94a3b8",
        reverseButtons: true,
        focusCancel: true,
        customClass: {
            popup: "rounded-3xl shadow-2xl",
            title: "font-black text-slate-900",
            htmlContainer: "text-slate-500 text-sm",
            confirmButton: "rounded-xl font-bold px-6 py-2.5 text-sm",
            cancelButton: "rounded-xl font-semibold px-6 py-2.5 text-sm",
            actions: "gap-3",
        },
    }).then((result) => result.isConfirmed);
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
