/**
 * usePresence.js - Live "who's editing this report" composable
 * -----------------------------------------------------------
 * Sends a heartbeat every 8s while the editor tab is open/visible and
 * receives the current list of active editors in the SAME response —
 * one request does both jobs, which keeps this cheap even with many
 * concurrent editors across many reports.
 *
 * Pauses heartbeats when the tab is hidden (battery/network friendly)
 * and resumes immediately when it becomes visible again.
 *
 * Sends a "leave" beacon on tab close / navigation so the badge updates
 * immediately instead of waiting out the server-side stale timeout.
 *
 * Usage:
 *   const { editors, totalCount } = usePresence(report.slug, authUserId)
 *   // editors: other people currently editing (current user excluded)
 *   // totalCount: total including the current user
 */
import { ref, onMounted, onBeforeUnmount } from "vue";

const HEARTBEAT_MS = 8000;

export function usePresence(reportSlug, currentUserId) {
    const editors = ref([]); // [{ id, name, initials, color, last_seen }]
    const totalCount = ref(0);
    let timer = null;
    let inFlight = false;

    function getCsrf() {
        return (
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content") || ""
        );
    }

    async function beat() {
        if (inFlight || document.visibilityState === "hidden") return;
        inFlight = true;
        try {
            const res = await fetch(
                `/reports/${reportSlug}/presence/heartbeat`,
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": getCsrf(),
                        Accept: "application/json",
                    },
                },
            );
            if (res.ok) {
                const data = await res.json();
                editors.value = (data.editors || []).filter(
                    (e) => e.id !== currentUserId,
                );
                totalCount.value = data.count || 0;
            }
        } catch {
            // Presence is a nice-to-have — never let a failed heartbeat
            // surface an error or interrupt editing.
        } finally {
            inFlight = false;
        }
    }

    function leaveBeacon() {
        if (!navigator.sendBeacon) return;
        const url = `/reports/${reportSlug}/presence/leave`;
        const params = new URLSearchParams({ _token: getCsrf() });
        // Blob with this content-type so Laravel parses `_token` correctly
        // out of a sendBeacon body (plain USVStrings arrive as text/plain).
        const blob = new Blob([params.toString()], {
            type: "application/x-www-form-urlencoded",
        });
        navigator.sendBeacon(url, blob);
    }

    function onVisibility() {
        if (document.visibilityState === "visible") beat();
    }

    onMounted(() => {
        beat();
        timer = setInterval(beat, HEARTBEAT_MS);
        window.addEventListener("beforeunload", leaveBeacon);
        document.addEventListener("visibilitychange", onVisibility);
    });

    onBeforeUnmount(() => {
        clearInterval(timer);
        window.removeEventListener("beforeunload", leaveBeacon);
        document.removeEventListener("visibilitychange", onVisibility);
        leaveBeacon();
    });

    return { editors, totalCount };
}
