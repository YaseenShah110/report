import "./bootstrap";
import "../css/app.css";
import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy/dist/index.js";
import "@fortawesome/fontawesome-free/css/all.min.css";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

// Global toast function
window.showToast = function (message, type = "success") {
    // Remove existing toast if any
    const existingToast = document.querySelector(".global-toast");
    if (existingToast) {
        existingToast.remove();
    }

    // Create toast element
    const toast = document.createElement("div");
    toast.className = `global-toast fixed bottom-6 right-6 z-[9999] flex items-center gap-3 px-5 py-3 rounded-xl shadow-2xl animate-slide-in-right`;

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
        success: '<i class="fa-solid fa-circle-check text-lg"></i>',
        error: '<i class="fa-solid fa-circle-exclamation text-lg"></i>',
        warning: '<i class="fa-solid fa-triangle-exclamation text-lg"></i>',
        info: '<i class="fa-solid fa-circle-info text-lg"></i>',
    };

    toast.innerHTML = `
    ${icons[type] || icons.success}
    <span class="text-sm font-medium">${message}</span>
    <button class="ml-2 hover:opacity-70 transition-opacity">
      <i class="fa-solid fa-xmark text-sm"></i>
    </button>
  `;

    document.body.appendChild(toast);

    // Add click handler to close button
    const closeBtn = toast.querySelector("button");
    closeBtn.addEventListener("click", () => {
        toast.remove();
    });

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
    from {
      opacity: 0;
      transform: translateX(100px);
    }
    to {
      opacity: 1;
      transform: translateX(0);
    }
  }
  
  @keyframes fade-out {
    from {
      opacity: 1;
      transform: translateX(0);
    }
    to {
      opacity: 0;
      transform: translateX(100px);
    }
  }
  
  .animate-slide-in-right {
    animation: slide-in-right 0.3s ease-out forwards;
  }
  
  .animate-fade-out {
    animation: fade-out 0.3s ease-out forwards;
  }
  
  /* Compact mode styles */
  .compact-mode .p-6 {
    padding: 1rem !important;
  }
  
  .compact-mode .gap-6 {
    gap: 1rem !important;
  }
  
  .compact-mode .mb-8 {
    margin-bottom: 1rem !important;
  }
  
  .compact-mode .p-5 {
    padding: 0.75rem !important;
  }
  
  .compact-mode .mt-4 {
    margin-top: 0.5rem !important;
  }
  
  /* Reduce motion */
  .reduce-motion *,
  .reduce-motion *::before,
  .reduce-motion *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
    scroll-behavior: auto !important;
  }
`;
document.head.appendChild(style);

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue"),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    // progress: {
        
    //     color: "#6366f1",
    //     showSpinner: true,
    // },
});
