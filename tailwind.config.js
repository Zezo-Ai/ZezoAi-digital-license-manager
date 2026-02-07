import sharedConfig from '@digital-license-manager/ui/tailwind.config'

/** @type {import('tailwindcss').Config} */
export default {
    ...sharedConfig,
    content: [
        './admin/**/*.{vue,js}',
        './node_modules/@digital-license-manager/ui/**/*.{vue,js}',
    ],
}
