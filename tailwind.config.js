module.exports = {
	corePlugins: {
		preflight: false,
	},
	important: false,
	content: [
		"./Resources/Private/**/*.{html,js}"
	],
	theme: {
		extend: {
		},
		fontFamily: {
			'sans': ["Open Sans","Helvetica Neue","Arial","sans-serif"]
		}
	},
	plugins: [
		require('@tailwindcss/forms'),
	],
}
