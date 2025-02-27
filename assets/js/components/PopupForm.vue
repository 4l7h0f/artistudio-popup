<template>
	<div>
		<h1>{{ isEdit ? "Edit Popup" : "Add Popup" }}</h1>
		<form @submit.prevent="savePopup">
			<input v-model="title" placeholder="Title" />
			<textarea
				v-model="description"
				placeholder="Description"
			></textarea>
			<input v-model="page" placeholder="Page" />
			<button type="submit">Save</button>
		</form>
	</div>
</template>

<script>
export default {
	data() {
		return {
			title: "",
			description: "",
			page: "",
			isEdit: false,
		};
	},
	created() {
		if (this.$route.params.id) {
			this.isEdit = true;
			// Fetch popup data for editing
		}
	},
	methods: {
		savePopup() {
			// Save or update popup
		},
	},
};
</script>
