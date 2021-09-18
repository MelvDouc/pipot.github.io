export function getHeaderHeight() {
    const header = document.querySelector("header");
    if (!header)
        return;
    const headerHeight = getComputedStyle(header).height;
    document.documentElement.style.setProperty("--header-height", headerHeight);
}
export function displayMessageTabs() {
    const messagesNav = document.getElementById("messages-nav");
    if (!messagesNav)
        return;
    const anchors = messagesNav.querySelectorAll("a");
    const sections = document.querySelectorAll("section");
    anchors.forEach(anchor => {
        anchor.addEventListener("click", (e) => {
            e.preventDefault();
            const dataLink = anchor.dataset.link;
            sections.forEach(section => {
                if (section.id === dataLink)
                    section.classList.remove("hidden");
                else
                    section.classList.add("hidden");
            });
        });
    });
}
