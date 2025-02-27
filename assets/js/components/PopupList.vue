<template>
	<div>
		<h1>Popups</h1>
		<ul>
			<li v-for="popup in popups" :key="popup.id">
				{{ popup.title }} - {{ popup.page }}
			</li>
		</ul>
	</div>
</template>

<script>
export default {
	data() {
		return {
			popups: [],
		};
	},
	async created() {
		const response = await fetch("/wp-json/artistudio/v1/popup", {
			headers: {
				"X-WP-Nonce": wpApiSettings.nonce,
			},
		});
		this.popups = await response.json();
	},
};
</script>
