document.addEventListener("click", function (e) {
    const el = e.target.closest("[data-analytics]");
    if (!el) return;

    const payload = {
        event: el.dataset.event,
        entity_type: el.dataset.entity,
        entity_id: el.dataset.id,
        source: el.dataset.source,
        page: window.location.pathname,
    };

    // ✅ الطريقة المثالية للتتبع
    if (navigator.sendBeacon) {
        const blob = new Blob([JSON.stringify(payload)], {
            type: "application/json",
        });
        navigator.sendBeacon("/analytics/track", blob);
    } else {
        // fallback
        fetch("/analytics/track", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content"),
            },
            body: JSON.stringify(payload),
            keepalive: true,
        });
    }
});
