<template>
	<!-- Loading State -->
	<div v-if="isLoading" class="popup-loading">Loading popups...</div>

	<!-- Error State -->
	<div v-else-if="error" class="popup-error">Error: {{ error }}</div>

	<!-- Popup Display -->
	<div v-else-if="showPopup && currentPopup" class="popup-overlay">
		<div class="popup-content">
			<h2>{{ currentPopup.title }}</h2>
			<div
				class="popup-description"
				v-html="currentPopup.description"
			></div>
			<div class="popup-footer">
				<span class="counter" v-if="totalPopups > 1">{{
					popupCounter
				}}</span>
				<button @click="closePopup">
					{{ currentIndex < totalPopups - 1 ? "Next" : "Close" }}
				</button>
			</div>
		</div>
	</div>
</template>

<script>
import { usePopup } from "../composables/usePopup";

export default {
	setup() {
		const {
			showPopup,
			currentPopup,
			currentIndex,
			totalPopups,
			popupCounter,
			closePopup,
			isLoading,
			error,
		} = usePopup();

		return {
			showPopup,
			currentPopup,
			currentIndex,
			totalPopups,
			popupCounter,
			isLoading,
			error,
			closePopup,
		};
	},
};
</script>

<style scoped>
.popup-overlay {
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background: rgba(0, 0, 0, 0.7);
	display: flex;
	justify-content: center;
	align-items: center;
	z-index: 9999;
}

.popup-content {
	background: white;
	padding: 2rem;
	border-radius: 8px;
	max-width: 90%;
	width: 600px;
	max-height: 90vh;
	overflow-y: auto;
}

/* Add these fallback styles for editor content */
.popup-description :deep(*) {
    margin: 1em 0;
    line-height: 1.6;
}

.popup-description :deep(ul),
.popup-description :deep(ol) {
    padding-left: 2em;
}

.popup-description :deep(img) {
    max-width: 100%;
    height: auto;
}

.popup-description :deep(a) {
    color: #0073aa;
    text-decoration: none;
}

.popup-description :deep(a:hover) {
    text-decoration: underline;
}

/* Add basic styles for WordPress specific elements */
.popup-description :deep(.wp-block-image) {
    margin: 1em 0;
}

.popup-description :deep(.wp-block-gallery) {
    display: flex;
    flex-wrap: wrap;
    gap: 1em;
}

.popup-description :deep(.wp-block-button__link) {
    background: #0073aa;
    color: white;
    padding: 0.5em 1em;
    border-radius: 4px;
    display: inline-block;
}

/* Add styles for editor content */
.popup-description :deep(*) {
	margin: 1em 0;
	line-height: 1.6;
}

.popup-description :deep(ul),
.popup-description :deep(ol) {
	padding-left: 2em;
}

.popup-description :deep(img) {
	max-width: 100%;
	height: auto;
}

.popup-description :deep(a) {
	color: #0073aa;
	text-decoration: none;
}

.popup-description :deep(a:hover) {
	text-decoration: underline;
}

.popup-footer {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-top: 1.5rem;
}

button {
	margin-top: 10px;
	padding: 10px 20px;
	background: #0073aa;
	color: white;
	border: none;
	border-radius: 4px;
	cursor: pointer;
}

button:hover {
	background: #005177;
}
</style>
