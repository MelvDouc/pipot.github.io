export function getHeaderHeight() {
    const header = document.querySelector("header");
    if (!header)
        return;
    const headerHeight = getComputedStyle(header).height;
    document.documentElement.style.setProperty("--header-height", headerHeight);
}
