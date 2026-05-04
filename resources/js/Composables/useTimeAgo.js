/**
 * useTimeAgo.js - Date Formatting Composable
 * -----------------------------------------------------------
 * Provides reactive date formatting functions for Vue components.
 *
 * Features:
 * - timeAgo: Shows relative time (e.g., "5 minutes ago", "2 hours ago")
 * - formatDate: Shows absolute date (e.g., "Jan 15, 2024")
 * - formatDateTime: Shows date and time (e.g., "Jan 15, 2024, 2:30 PM")
 * - Auto-updates every 60 seconds so "time ago" stays accurate
 *
 * Usage:
 * const { timeAgo, formatDate } = useTimeAgo()
 * timeAgo(notification.created_at) // "3 minutes ago"
 */

import { ref, onBeforeUnmount } from "vue";

export function useTimeAgo() {
    // Reactive current timestamp - updates every 60 seconds
    const now = ref(Date.now());

    // Update the timestamp every minute to keep "time ago" accurate
    const timer = setInterval(() => {
        now.value = Date.now();
    }, 60000); // 60,000 milliseconds = 1 minute

    // Clean up timer when component is destroyed
    onBeforeUnmount(() => clearInterval(timer));

    /**
     * Convert a date to relative time string
     * Examples: "Just now", "5m ago", "3h ago", "2d ago", "1 week ago"
     * Falls back to absolute date for dates older than 1 week
     */
    const timeAgo = (date) => {
        if (!date) return "Never";

        const seconds = Math.floor(
            (now.value - new Date(date).getTime()) / 1000,
        );

        // Future dates
        if (seconds < 0) return "Just now";

        // Time intervals from largest to smallest
        const intervals = [
            { label: "year", seconds: 31536000 },
            { label: "month", seconds: 2592000 },
            { label: "week", seconds: 604800 },
            { label: "day", seconds: 86400 },
            { label: "hour", seconds: 3600 },
            { label: "minute", seconds: 60 },
        ];

        // Find the first interval that fits
        for (const interval of intervals) {
            const count = Math.floor(seconds / interval.seconds);
            if (count >= 1) {
                return `${count} ${interval.label}${count !== 1 ? "s" : ""} ago`;
            }
        }

        return "Just now";
    };

    /**
     * Format a date as a short date string
     * Example: "Jan 15, 2024"
     */
    const formatDate = (date) => {
        if (!date) return "N/A";
        return new Date(date).toLocaleDateString("en-US", {
            month: "short",
            day: "numeric",
            year: "numeric",
        });
    };

    /**
     * Format a date with both date and time
     * Example: "Jan 15, 2024, 2:30 PM"
     */
    const formatDateTime = (date) => {
        if (!date) return "N/A";
        return new Date(date).toLocaleString("en-US", {
            month: "short",
            day: "numeric",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        });
    };

    // Return all functions for use in components
    return {
        timeAgo,
        formatDate,
        formatDateTime,
    };
}
