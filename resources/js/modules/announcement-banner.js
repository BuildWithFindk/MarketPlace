const onReady = (callback) => {
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", callback, { once: true });

        return;
    }

    callback();
};

onReady(() => {
    document.querySelectorAll(".announcement-banner").forEach((banner) => {
        const id = banner.dataset.announcementId;
        const storageKey = `announcement_banner_closed_${id}`;
        const closeButton = banner.querySelector(".announcement-banner__close");

        if (!id || !closeButton) {
            return;
        }

        if (localStorage.getItem(storageKey) === "1") {
            banner.remove();

            return;
        }

        closeButton.addEventListener("click", () => {
            localStorage.setItem(storageKey, "1");
            banner.remove();
        });
    });
});
