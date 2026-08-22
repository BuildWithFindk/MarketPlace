import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./app/**/*.php",
        "./Modules/**/*.php",
        "./Modules/**/*.blade.php",
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            colors: {
                primary: "var(--color-primary)",
                "primary-hover": "var(--color-primary-hover)",
                secondary: "var(--color-secondary)",
                accent: "var(--color-accent)",
                surface: "var(--color-surface)",
                text: "var(--color-text)",
                blue: {
                    50: "#e7f3ff",
                    100: "#dbeafe",
                    200: "#bfdbfe",
                    300: "#93c5fd",
                    400: "#60a5fa",
                    500: "#1877f2",
                    600: "#166fe5",
                    700: "#1d4ed8",
                    800: "#1e40af",
                    900: "#1e3a8a",
                },
            },
            fontFamily: {
                sans: [
                    "-apple-system",
                    "BlinkMacSystemFont",
                    "SF Pro Text",
                    "SF Pro Display",
                    "Helvetica Neue",
                    "Helvetica",
                    "Arial",
                    ...defaultTheme.fontFamily.sans,
                ],
            },
            fontSize: {
                xs: ["0.75rem", { lineHeight: "1rem" }],
                sm: ["0.875rem", { lineHeight: "1.25rem" }],
                base: ["1rem", { lineHeight: "1.55rem" }],
                lg: ["1.0625rem", { lineHeight: "1.6rem" }],
                xl: ["1.125rem", { lineHeight: "1.65rem" }],
                "2xl": ["1.35rem", { lineHeight: "1.4" }],
                "3xl": ["1.65rem", { lineHeight: "1.2" }],
                "4xl": ["1.95rem", { lineHeight: "1.1" }],
                "5xl": ["2.45rem", { lineHeight: "1.05" }],
                "6xl": ["3.1rem", { lineHeight: "1" }],
            },
        },
    },

    plugins: [forms],
};
