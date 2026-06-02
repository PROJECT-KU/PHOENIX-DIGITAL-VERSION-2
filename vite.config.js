import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/css/app.scss",
                "resources/css/public-custom-styles.css",
                "resources/js/public-custom-scripts.js",
            ],
            refresh: true,
        }),
    ],
});
