export function useTheme() {
    const applyTheme = (dark) => {
        document.documentElement.classList.toggle("dark", dark);
        localStorage.setItem("theme", dark ? "dark" : "light");
    };

    const initTheme = () => {
        const saved = localStorage.getItem("theme");
        const prefersDark = window.matchMedia(
            "(prefers-color-scheme: dark)",
        ).matches;
        applyTheme(saved ? saved === "dark" : prefersDark);
    };

    const toggleDark = () => {
        const isDark = document.documentElement.classList.contains("dark");
        applyTheme(!isDark);
    };

    return { initTheme, toggleDark };
}
