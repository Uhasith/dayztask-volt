import forms from "@tailwindcss/forms";
import typography from "@tailwindcss/typography";
import wireuiConfig from "./vendor/wireui/wireui/tailwind.config.js";
import filamentConfig from "./vendor/filament/support/tailwind.config.preset";
const colors = require("tailwindcss/colors");

/** @type {import('tailwindcss').Config} */
export default {
    presets: [wireuiConfig, filamentConfig],
    darkMode: "class",
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./vendor/laravel/jetstream/**/*.blade.php",
        "./vendor/robsontenorio/mary/src/View/Components/**/*.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./app/Filament/**/*.php",
        "./resources/views/filament/**/*.blade.php",
        "./vendor/filament/**/*.blade.php",
        "./vendor/wireui/wireui/src/*.php",
        "./vendor/wireui/wireui/ts/**/*.ts",
        "./vendor/wireui/wireui/src/WireUi/**/*.php",
        "./vendor/wireui/wireui/src/Components/**/*.php",
    ],
    theme: {
        extend: {
            colors: {
                primary: colors.amber,
                secondary: colors.gray,
                positive: colors.emerald,
                negative: colors.red,
                warning: colors.amber,
                info: colors.blue,
            },
        },
    },
    daisyui: {
        themes: [
            {
                light: {
                    ...require("daisyui/src/theming/themes")["light"],
                    primary: "#f59e0b",
                },
                dark: {
                    ...require("daisyui/src/theming/themes")["dark"],
                    primary: "#f59e0b",
                },
            },
        ],
    },
    plugins: [forms, typography, require("daisyui")],
};
