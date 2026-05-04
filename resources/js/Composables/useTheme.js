/**
 * useTheme.js - Theme Management Composable
 * -----------------------------------------------------------
 * Manages application theme settings with localStorage persistence.
 *
 * Features:
 * - Light/Dark mode toggle with system preference detection
 * - Accent color customization (8 color options)
 * - Font family selection (3 font options)
 * - Font size adjustment (12px - 18px)
 * - Border radius adjustment (4px - 24px)
 * - Compact mode toggle
 * - Animations toggle
 *
 * All settings are persisted in localStorage and applied immediately.
 * Settings survive page refreshes.
 */

import { ref, watch, onMounted } from "vue";

export function useTheme() {
    // Reactive theme settings
    const isDark = ref(false); // Current theme (light/dark)
    const accentColor = ref("#6366f1"); // Accent color (hex)
    const borderRadius = ref(12); // Card border radius (px)
    const fontSize = ref(14); // Base font size (px)
    const fontFamily = ref("'Inter', sans-serif"); // Font family
    const compactMode = ref(false); // Compact mode toggle
    const animationsEnabled = ref(true); // Animations toggle

    /**
     * Apply all current theme settings to the DOM
     * Sets CSS variables, class names, and inline styles
     */
    const applyTheme = () => {
        // Dark mode - toggle 'dark' class on <html> element
        document.documentElement.classList.toggle("dark", isDark.value);

        // CSS custom properties for dynamic styling
        document.documentElement.style.setProperty(
            "--accent-color",
            accentColor.value,
        );
        document.documentElement.style.setProperty(
            "--accent-light",
            accentColor.value + "cc",
        );
        document.documentElement.style.setProperty(
            "--card-radius",
            borderRadius.value + "px",
        );
        document.documentElement.style.setProperty(
            "--font-family",
            fontFamily.value,
        );

        // Apply font to body
        document.body.style.fontFamily = fontFamily.value;
        document.body.style.fontSize = fontSize.value + "px";

        // Compact mode - adds 'compact-mode' class to body
        document.body.classList.toggle("compact-mode", compactMode.value);

        // Animations - adds 'reduce-motion' class when disabled
        document.body.classList.toggle(
            "reduce-motion",
            !animationsEnabled.value,
        );
    };

    /**
     * Load all settings from localStorage
     * Falls back to defaults if no saved settings found
     */
    const loadSettings = () => {
        // Theme: 'dark', 'light', or null (use system preference)
        const savedTheme = localStorage.getItem("theme");
        if (savedTheme === "dark") {
            isDark.value = true;
        } else if (savedTheme === "light") {
            isDark.value = false;
        } else {
            // No saved preference - use system preference
            isDark.value = window.matchMedia(
                "(prefers-color-scheme: dark)",
            ).matches;
        }

        // Load other settings with fallback defaults
        accentColor.value = localStorage.getItem("accent-color") || "#6366f1";
        borderRadius.value = parseInt(
            localStorage.getItem("border-radius") || "12",
        );
        fontSize.value = parseInt(localStorage.getItem("font-size") || "14");
        fontFamily.value =
            localStorage.getItem("font-family") || "'Inter', sans-serif";
        compactMode.value = localStorage.getItem("compact-mode") === "true";
        animationsEnabled.value =
            localStorage.getItem("animations") !== "false";

        // Apply loaded settings to DOM
        applyTheme();
    };

    /**
     * Save all current settings to localStorage
     */
    const saveSettings = () => {
        localStorage.setItem("theme", isDark.value ? "dark" : "light");
        localStorage.setItem("accent-color", accentColor.value);
        localStorage.setItem("border-radius", borderRadius.value.toString());
        localStorage.setItem("font-size", fontSize.value.toString());
        localStorage.setItem("font-family", fontFamily.value);
        localStorage.setItem("compact-mode", compactMode.value.toString());
        localStorage.setItem("animations", animationsEnabled.value.toString());
    };

    // ── Individual setting toggles ──────────────────────────────
    const toggleDark = () => {
        isDark.value = !isDark.value;
        saveSettings();
        applyTheme();
    };

    const setAccentColor = (color) => {
        accentColor.value = color;
        saveSettings();
        applyTheme();
    };

    const setBorderRadius = (radius) => {
        borderRadius.value = radius;
        saveSettings();
        applyTheme();
    };

    const setFontSize = (size) => {
        fontSize.value = size;
        saveSettings();
        applyTheme();
    };

    const setFontFamily = (font) => {
        fontFamily.value = font;
        saveSettings();
        applyTheme();
    };

    const toggleCompactMode = () => {
        compactMode.value = !compactMode.value;
        saveSettings();
        applyTheme();
    };

    const toggleAnimations = () => {
        animationsEnabled.value = !animationsEnabled.value;
        saveSettings();
        applyTheme();
    };

    // ── System theme change listener ────────────────────────────
    // Only applies if user hasn't manually set a theme preference
    if (typeof window !== "undefined") {
        window
            .matchMedia("(prefers-color-scheme: dark)")
            .addEventListener("change", (e) => {
                if (!localStorage.getItem("theme")) {
                    isDark.value = e.matches;
                    applyTheme();
                }
            });
    }

    // Load settings when composable is first used
    onMounted(loadSettings);

    // Return all functions and state for use in components
    return {
        isDark,
        accentColor,
        borderRadius,
        fontSize,
        fontFamily,
        compactMode,
        animationsEnabled,
        toggleDark,
        setAccentColor,
        setBorderRadius,
        setFontSize,
        setFontFamily,
        toggleCompactMode,
        toggleAnimations,
        applyTheme,
        loadSettings,
        saveSettings,
    };
}
