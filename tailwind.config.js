import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: "class",
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },

            colors: {
                brand: {
                    DEFAULT: "#7c3aed",
                    light: "#a855f7",
                    pink: "#ec4899",
                    indigo: "#6366f1",
                },
            },
        },
    },

    safelist: [
        "scenario-icon-purple",
        "scenario-icon-pink",
        "scenario-icon-indigo",
    ],

    plugins: [forms],
};
