<template>
	<div class="wrap">
		<h1 class="wp-heading-inline">Artistudio Popups</h1>
		<router-link to="/create" class="page-title-action">
			Add New Popup
		</router-link>
		<hr class="wp-header-end" />
		<!-- Notification -->
		<div
			id="message"
			v-if="popupsNotification.message"
			:class="['notification', popupsNotification.type]"
			class="notice is-dismissible updated"
		>
			<p>{{ popupsNotification.message }}</p>
		</div>

		<div
			id="message"
			v-if="globalNotification.message"
			:class="['notification', globalNotification.type]"
			class="notice is-dismissible updated"
		>
			<p>{{ globalNotification.message }}</p>
		</div>

		<div class="subsubsub tablenav top">
			<!-- Status Filter -->
			<div class="alignleft actions filters">
				<select v-model="filters.status" @change="fetchPopups">
					<option value="">All Statuses</option>
					<option value="publish">Published</option>
					<option value="draft">Draft</option>
					<option value="trash">Trash</option>
				</select>
			</div>
		</div>

		<p class="search-box">
			<label class="screen-reader-text" for="post-search-input"
				>Search Posts:</label
			>
			<!-- Search Filter -->
			<input
				class="search-box"
				id="post-search-input"
				type="text"
				v-model="filters.search"
				@input="fetchPopups"
				placeholder="Search..."
			/>
			<button @click="clearSearch" id="search-submit" class="button">
				Clear Search
			</button>
		</p>
		<div class="tablenav top">
			<!-- Bulk Actions Dropdown -->
			<div class="alignleft actions bulkactions">
				<select v-model="bulkAction" @change="handleBulkAction">
					<option value="">Bulk Actions</option>
					<option v-if="filters.status !== 'trash'" value="trash">
						Move to Trash
					</option>
					<option v-if="filters.status === 'trash'" value="restore">
						Restore
					</option>
					<option v-if="filters.status === 'trash'" value="delete">
						Delete Permanently
					</option>
					<option v-if="filters.status !== 'trash'" value="edit">
						Edit
					</option>
				</select>
			</div>
			<div class="alignleft actions">
				<!-- Month Filter -->
				<select v-model="filters.month" @change="fetchPopups">
					<option value="">All Dates</option>
					<option
						v-for="month in availableMonths"
						:key="month.value"
						:value="month.value"
					>
						{{ month.label }}
					</option>
				</select>
			</div>
			<div class="alignleft actions"></div>
			<div class="tablenav-pages">
				<span class="pagination-links" v-if="shouldShowPagination">
					<button @click="prevPage" :disabled="currentPage === 1">
						Previous
					</button>
					<span class="current-page">{{ currentPage }}</span>
					<button
						@click="nextPage"
						:disabled="currentPage * perPage >= totalPopups"
					>
						Next
					</button>
				</span>
				<span class="displaying-num">{{ totalPopups }} items</span>
			</div>
			<br class="clear" />
		</div>
		<h2 class="screen-reader-text">Posts list</h2>
		<table
			class="wp-list-table widefat fixed striped table-view-excerpt posts"
		>
			<caption class="screen-reader-text">
				Table ordered by Date. Descending.
			</caption>
			<thead>
				<tr>
					<td id="cb" class="manage-column column-cb check-column">
						<input
							type="checkbox"
							v-model="selectAll"
							@change="toggleSelectAll"
						/>
					</td>
					<th
						scope="col"
						id="title"
						class="manage-column column-title column-primary sortable desc"
						abbr="Title"
						@click="sortByColumn('title')"
					>
						Title
						<span
							v-if="sortBy.column === 'title'"
							class="sorting-indicators"
						>
							{{ sortBy.direction === "asc" ? "▲" : "▼" }}
						</span>
					</th>
					<th scope="col" id="page" class="manage-column column-page">
						Page
					</th>
					<th
						scope="col"
						id="date"
						class="manage-column column-date sorted desc"
						aria-sort="descending"
						abbr="Date"
						@click="sortByColumn('date')"
					>
						Date
						<span
							v-if="sortBy.column === 'date'"
							class="sorting-indicators"
						>
							{{ sortBy.direction === "asc" ? "▲" : "▼" }}
						</span>
					</th>
				</tr>
			</thead>
			<tbody id="the-list">
				<tr
					v-if="popups.length > 0"
					v-for="popup in sortedPopups"
					:key="popup.id"
					class="iedit"
				>
					<th scope="row" class="check-column">
						<input
							type="checkbox"
							v-model="selectedPopups"
							:value="popup.id"
						/>
					</th>
					<td
						class="title column-title has-row-actions column-primary page-title"
						data-colname="Title"
					>
						<strong>
							<router-link
								:to="`/edit/${popup.id}`"
								class="row title"
							>
								{{ popup.title }}
							</router-link>
						</strong>
						<div
							class="popup-description"
							v-html="popup.description"
						></div>
					</td>
					<td class="page column-page" data-colname="page">
						{{ pageTitles[popup.page] || "Loading..." }}
					</td>
					<td class="date column-date" data-colname="Date">
						{{ popup.status }} <br />
						{{ popup.formatted_date }}
					</td>
				</tr>
				<tr v-else>
					<td colspan="4"><p>There is no data available.</p></td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td id="cb" class="manage-column column-cb check-column">
						<input
							type="checkbox"
							v-model="selectAll"
							@change="toggleSelectAll"
						/>
					</td>
					<th
						scope="col"
						id="title"
						class="manage-column column-title column-primary sortable desc"
						abbr="Title"
						@click="sortByColumn('title')"
					>
						Title
						<span
							v-if="sortBy.column === 'title'"
							class="sorting-indicators"
						>
							{{ sortBy.direction === "asc" ? "▲" : "▼" }}
						</span>
					</th>
					<th scope="col" id="page" class="manage-column column-page">
						Page
					</th>
					<th
						scope="col"
						id="date"
						class="manage-column column-date sorted desc"
						aria-sort="descending"
						abbr="Date"
						@click="sortByColumn('date')"
					>
						Date
						<span
							v-if="sortBy.column === 'date'"
							class="sorting-indicators"
						>
							{{ sortBy.direction === "asc" ? "▲" : "▼" }}
						</span>
					</th>
				</tr>
			</tfoot>
		</table>
		<div class="tablenav bottom">
			<!-- Status Filter -->
			<div class="alignleft actions bulkactions">
				<select v-model="filters.status" @change="fetchPopups">
					<option value="">All Statuses</option>
					<option value="publish">Published</option>
					<option value="draft">Draft</option>
					<option value="trash">Trash</option>
				</select>
			</div>
			<div class="alignleft actions"></div>
			<div class="tablenav-pages one-page">
				<span class="displaying-num">{{ popups.length }} items</span>
			</div>
			<br class="clear" />
		</div>
		<div id="ajax-response"></div>
		<div class="clear"></div>
	</div>
</template>

<script>
import { usePopups } from "../composables/usePopups";
import { useNotification } from "../composables/useNotification";
import { ref, watch, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";

export default {
	setup() {
		const {
			popups,
			selectedPopups,
			selectAll,
			filters,
			availableMonths,
			bulkAction,
			notification: popupsNotification,
			sortBy,
			sortedPopups,
			clearSearch,
			fetchPopups,
			fetchPages,
			toggleSelectAll,
			handleBulkAction,
			goToEdit,
			sortByColumn,
			perPage,
			currentPage,
			totalPopups,
		} = usePopups();

		const route = useRoute();
		const router = useRouter();
		const { notification: globalNotification, showNotification } =
			useNotification();
		const pageTitles = ref({});

		// Check for notification query parameter when the component is mounted
		if (route.query.notification === "success") {
			showNotification(route.query.message, "success");
			// Clear the query parameter after showing the notification
			router.replace({ query: {} });
		}

		const fetchAllData = async () => {
			await fetchPopups();
			const pages = await fetchPages();
			pages.forEach((page) => {
				pageTitles.value[page.id] = page.title;
			});
		};

		const nextPage = () => {
			currentPage.value++;
			fetchPopups();
		};

		const prevPage = () => {
			currentPage.value--;
			fetchPopups();
		};

		watch(
			filters,
			() => {
				currentPage.value = 1;
				fetchPopups();
			},
			{ deep: true }
		);

		const shouldShowPagination = computed(() => {
			return totalPopups.value > perPage.value;
		});

		const isNextPageDisabled = computed(() => {
			return currentPage.value * perPage.value >= totalPopups.value;
		});

		onMounted(fetchAllData);

		return {
			popups,
			selectedPopups,
			selectAll,
			filters,
			availableMonths,
			bulkAction,
			popupsNotification,
			globalNotification,
			sortBy,
			sortedPopups,
			clearSearch,
			fetchPages,
			fetchPopups,
			toggleSelectAll,
			handleBulkAction,
			goToEdit,
			sortByColumn,
			pageTitles,
			nextPage,
			prevPage,
			perPage,
			currentPage,
			totalPopups,
			isNextPageDisabled,
			shouldShowPagination,
		};
	},
};
</script>

<style scoped>
/* Add styles for sorting indicators */
th {
	cursor: pointer;
	user-select: none;
}

th span {
	margin-left: 5px;
	font-size: 0.8em;
}

/* Style for Clear Search button */
button {
	margin-left: 10px;
	padding: 5px 10px;
	background-color: #f0f0f0;
	border: 1px solid #ccc;
	border-radius: 4px;
	cursor: pointer;
}

button:hover {
	background-color: #e0e0e0;
}

.filters {
	margin-bottom: 20px;
}

.filters select,
.filters input {
	margin-right: 10px;
}

table {
	width: 100%;
	border-collapse: collapse;
	margin-top: 20px;
}

th,
td {
	padding: 10px;
	border: 1px solid #ddd;
	text-align: left;
}

button {
	margin-right: 5px;
}

p {
	margin-top: 20px;
	font-style: italic;
	color: #888;
}

.tablenav-pages {
	display: flex;
	align-items: center;
	justify-content: flex-end;
	margin-top: 10px;
}

.pagination-links {
	margin-left: 10px;
}

.pagination-links button {
	margin: 0 5px;
	padding: 5px 10px;
	background-color: #f0f0f0;
	border: 1px solid #ccc;
	border-radius: 4px;
	cursor: pointer;
}

.pagination-links button:disabled {
	background-color: #e0e0e0;
	cursor: not-allowed;
}

.current-page {
	margin: 0 10px;
}

.popup-description {
    margin-top: 8px;
    font-size: 0.9em;
    color: #555;
    line-height: 1.4;
}

.popup-description :deep(*) {
    margin: 0.5em 0;
}

.popup-description :deep(ul),
.popup-description :deep(ol) {
    padding-left: 1.5em;
}

.popup-description :deep(img) {
    max-width: 100%;
    height: auto;
}

</style>
