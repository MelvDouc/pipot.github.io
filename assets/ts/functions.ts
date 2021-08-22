export function getHeaderHeight(): void {
  const header: HTMLElement | null = document.querySelector("header");
  if (!header)
    return;
  const headerHeight: string = getComputedStyle(header).height;
  document.documentElement.style.setProperty("--header-height", headerHeight);
}