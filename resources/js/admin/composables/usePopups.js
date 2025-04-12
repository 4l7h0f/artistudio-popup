// src/composables/usePopups.js
import { ref, computed } from 'vue';
import { useNotification } from './useNotification';
import { useRouter } from "vue-router";

export function usePopups() {
    const { notification, showNotification } = useNotification();

    const popups = ref([]);
    const selectedPopups = ref([]);
    const selectAll = ref(false);
    const filters = ref({
        status: "",
        month: "",
        search: "",
    });
    const availableMonths = ref([]);
    const bulkAction = ref("");
    const sortBy = ref({
        column: "",
        direction: "asc",
    });
    const perPage = ref(artistudioPopupAdmin.per_page);
    const currentPage = ref(1);
    const totalPopups = ref(0);
    const router = useRouter();

    const sortedPopups = computed(() => {
        if (!sortBy.value.column) return popups.value;

        return [...popups.value].sort((a, b) => {
            let valueA = a[sortBy.value.column];
            let valueB = b[sortBy.value.column];

            // Handle date sorting
            if (sortBy.value.column === "date") {
                valueA = new Date(a.date).getTime();
                valueB = new Date(b.date).getTime();
            }

            // Handle string sorting (case-insensitive)
            if (typeof valueA === "string") {
                valueA = valueA.toLowerCase();
                valueB = valueB.toLowerCase();
            }

            return valueA < valueB ? (sortBy.value.direction === "asc" ? -1 : 1) :
                valueA > valueB ? (sortBy.value.direction === "asc" ? 1 : -1) : 0;
        });
    });

    const clearSearch = () => {
        filters.value.search = "";
        fetchPopups();
    };

    const sortByColumn = (column) => {
        if (sortBy.value.column === column) {
            // Toggle sorting direction if the same column is clicked
            sortBy.value.direction = sortBy.value.direction === "asc" ? "desc" : "asc";
        } else {
            // Sort by the new column in ascending order by default
            sortBy.value.column = column;
            sortBy.value.direction = "asc";
        }
    };

    const fetchPages = async () => {
        const response = await fetch(
            `${artistudioPopupAdmin.rest_url}pages`,
            {
                headers: {
                    "X-WP-Nonce": artistudioPopupAdmin.nonce,
                },
            }
        );

        if (!response.ok) {
            throw new Error('Failed to fetch pages');
        }

        return await response.json();
    };

    const fetchPopups = async () => {
        const params = new URLSearchParams();
        if (filters.value.status) params.append("status", filters.value.status);
        if (filters.value.month) params.append("month", filters.value.month);
        if (filters.value.search) params.append("search", filters.value.search);
        params.append("per_page", perPage.value);
        params.append("page", currentPage.value);
    
        const response = await fetch(
            `${artistudioPopupAdmin.rest_url}popup?${params.toString()}`,
            {
                headers: {
                    "X-WP-Nonce": artistudioPopupAdmin.nonce,
                },
            }
        );
        const data = await response.json();
        popups.value = data.data;
        totalPopups.value = data.total;
        updateAvailableMonths();
    };

    const updateAvailableMonths = () => {
        // Extract unique months from popups
        const months = new Set();
        popups.value.forEach((popup) => {
            const date = new Date(popup.date);
            const month = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}`;
            months.add(month);
        });

        // Convert Set to array of { value, label } objects
        availableMonths.value = Array.from(months).map((month) => {
            const [year, monthNumber] = month.split("-");
            const monthName = new Date(year, monthNumber - 1).toLocaleString("default", { month: "long" });
            return {
                value: month,
                label: `${monthName} ${year}`,
            };
        });
    };

    const toggleSelectAll = () => {
        if (selectAll.value) {
            selectedPopups.value = popups.value.map((popup) => popup.id);
        } else {
            selectedPopups.value = [];
        }
    };

    const handleBulkAction = async () => {
        if (bulkAction.value === "trash") {
            await bulkMoveToTrash();
        } else if (bulkAction.value === "restore") {
            await bulkRestore();
        } else if (bulkAction.value === "delete") {
            await bulkDeletePermanently();
        } else if (bulkAction.value === "edit") {
            bulkEdit();
        }
        bulkAction.value = "";
    };

    const bulkMoveToTrash = async () => {
        if (selectedPopups.value.length === 0) {
            showNotification("Please select at least one popup.", "error");
            return;
        }

        if (confirm("Are you sure you want to move the selected popups to trash?")) {
            for (const popupId of selectedPopups.value) {
                await fetch(`${artistudioPopupAdmin.rest_url}popup/${popupId}/trash`, {
                    method: "POST",
                    headers: {
                        "X-WP-Nonce": artistudioPopupAdmin.nonce,
                    },
                });
            }
            showNotification("Selected popups moved to trash successfully.", "success");
            fetchPopups();
        }
    };

    const bulkEdit = () => {
        if (selectedPopups.value.length === 1) {
            goToEdit(selectedPopups.value[0]);
        } else {
            alert("Please select only one popup to edit.");
        }
    };

    const bulkRestore = async () => {
        if (selectedPopups.value.length === 0) {
            showNotification("Please select at least one popup.", "error");
            return;
        }

        if (confirm("Are you sure you want to restore the selected popups?")) {
            for (const popupId of selectedPopups.value) {
                await fetch(`${artistudioPopupAdmin.rest_url}popup/${popupId}/restore`, {
                    method: "POST",
                    headers: {
                        "X-WP-Nonce": artistudioPopupAdmin.nonce,
                    },
                });
            }
            fetchPopups();
            showNotification("Selected popups restored successfully.", "success");
        }
    };

    const bulkDeletePermanently = async () => {
        if (selectedPopups.value.length === 0) {
            showNotification("Please select at least one popup.", "error");
            return;
        }

        if (confirm("Are you sure you want to permanently delete the selected popups?")) {
            for (const popupId of selectedPopups.value) {
                await fetch(`${artistudioPopupAdmin.rest_url}popup/${popupId}/delete`, {
                    method: "DELETE",
                    headers: {
                        "X-WP-Nonce": artistudioPopupAdmin.nonce,
                    },
                });
            }
            fetchPopups();
            showNotification("Selected popups deleted permanently.", "success");
        }
    };

    const goToEdit = (id) => router.push(`/edit/${id}`);

    return {
        popups,
        selectedPopups,
        selectAll,
        filters,
        availableMonths,
        bulkAction,
        notification,
        sortBy,
        sortedPopups,
        clearSearch,
        fetchPages,
        fetchPopups,
        toggleSelectAll,
        handleBulkAction,
        sortByColumn,
        perPage,
        currentPage,
        totalPopups,
        goToEdit
    };
}