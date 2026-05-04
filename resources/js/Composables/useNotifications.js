/**
 * useNotifications.js - Notification Polling Composable
 * -----------------------------------------------------------
 * Manages real-time notification fetching and state.
 *
 * Features:
 * - Auto-fetches notifications on mount
 * - Polls for new notifications every 30 seconds
 * - Mark single notification as read
 * - Mark all notifications as read
 * - Delete (soft-delete) notifications
 * - Handles loading and error states
 * - Cleans up polling on component unmount
 *
 * Usage:
 * const { notifications, unreadCount, markAsRead, markAllAsRead } = useNotifications()
 */

import { ref, onMounted, onUnmounted } from "vue";
import axios from "axios";

export function useNotifications() {
    // Reactive state
    const notifications = ref([]); // Array of notification objects
    const unreadCount = ref(0); // Number of unread notifications
    const loading = ref(false); // Loading state
    const error = ref(null); // Error message if fetch fails
    let pollingInterval = null; // Interval ID for polling

    /**
     * Fetch latest notifications from the server
     * Updates both the notifications array and unread count
     */
    const fetchNotifications = async () => {
        try {
            loading.value = true;
            error.value = null;

            // Fetch from the API endpoint
            const response = await axios.get("/notifications/latest");

            // Update state with response data
            notifications.value = response.data.notifications || [];
            unreadCount.value = response.data.unread_count || 0;
        } catch (e) {
            console.error("Failed to fetch notifications:", e);
            // Only show error if we don't have any notifications loaded yet
            if (notifications.value.length === 0) {
                error.value = "Failed to load notifications";
            }
        } finally {
            loading.value = false;
        }
    };

    /**
     * Mark a single notification as read
     * Updates local state immediately without waiting for next poll
     */
    const markAsRead = async (id) => {
        try {
            await axios.put(`/notifications/${id}/read`);

            // Update local state
            const notification = notifications.value.find((n) => n.id === id);
            if (notification) {
                notification.read_at = new Date().toISOString();
            }

            // Decrement unread count
            unreadCount.value = Math.max(0, unreadCount.value - 1);
        } catch (e) {
            console.error("Failed to mark notification as read:", e);
        }
    };

    /**
     * Mark ALL notifications as read
     */
    const markAllAsRead = async () => {
        try {
            await axios.put("/notifications/mark-all-read");

            // Update all local notifications as read
            notifications.value.forEach((n) => {
                if (!n.read_at) n.read_at = new Date().toISOString();
            });

            // Reset unread count
            unreadCount.value = 0;
        } catch (e) {
            console.error("Failed to mark all notifications as read:", e);
        }
    };

    /**
     * Soft delete a notification
     * Removes it from the local array immediately
     */
    const deleteNotification = async (id) => {
        try {
            await axios.delete(`/notifications/${id}`);

            // Remove from local state immediately for responsive UI
            notifications.value = notifications.value.filter(
                (n) => n.id !== id,
            );
        } catch (e) {
            console.error("Failed to delete notification:", e);
        }
    };

    /**
     * Start polling for notifications
     * Fetches immediately, then every 30 seconds
     */
    const startPolling = (interval = 30000) => {
        stopPolling(); // Clear any existing interval
        fetchNotifications(); // Immediate first fetch
        pollingInterval = setInterval(fetchNotifications, interval);
    };

    /**
     * Stop polling for notifications
     * Clears the interval timer
     */
    const stopPolling = () => {
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
    };

    // ── Lifecycle hooks ──────────────────────────────────────────
    onMounted(() => {
        fetchNotifications(); // Fetch immediately on mount
        startPolling(); // Start 30-second polling
    });

    // Clean up polling when component is destroyed
    onUnmounted(stopPolling);

    // Return all state and functions
    return {
        notifications,
        unreadCount,
        loading,
        error,
        fetchNotifications,
        markAsRead,
        markAllAsRead,
        deleteNotification,
        startPolling,
        stopPolling,
    };
}
