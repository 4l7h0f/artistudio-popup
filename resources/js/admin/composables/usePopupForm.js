import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import { useNotification } from "./useNotification";

export default function usePopupForm() {
    const router = useRouter();
    const { showNotification } = useNotification();
    const pages = ref([]);
    const formData = ref({ 
        id: null, 
        title: "", 
        description: "", 
        page: "", 
        status: "publish" // Set default status
    });

    const isEditMode = computed(() => !!formData.value.id);

    const fetchPages = async () => {
        const response = await fetch(`${artistudioPopupAdmin.rest_url}pages`, {
            headers: { "X-WP-Nonce": artistudioPopupAdmin.nonce },
        });
        pages.value = await response.json();
    };

    const fetchPopup = async (id) => {
        if (id) {
            const response = await fetch(`${artistudioPopupAdmin.rest_url}popup/${id}`, {
                headers: { "X-WP-Nonce": artistudioPopupAdmin.nonce },
            });
            const data = await response.json();
            formData.value = {
                ...data,
                // Ensure description is properly handled
                description: data.description || ""
            };
        }
    };

    const submitForm = async () => {
        const url = `${artistudioPopupAdmin.rest_url}popup${isEditMode.value ? `/${formData.value.id}` : ""}`;
        const method = isEditMode.value ? "PUT" : "POST";

        try {
            const response = await fetch(url, {
                method,
                headers: { 
                    "Content-Type": "application/json", 
                    "X-WP-Nonce": artistudioPopupAdmin.nonce 
                },
                body: JSON.stringify(formData.value),
            });

            if (response.ok) {
                router.push({
                    path: "/list",
                    query: {
                        notification: "success",
                        message: isEditMode.value 
                            ? "Popup updated successfully!" 
                            : "Popup created successfully!",
                    },
                });
            } else {
                const errorData = await response.json();
                showNotification(`Failed to save popup: ${errorData.message}`, "error");
            }
        } catch (error) {
            showNotification("An error occurred while saving the popup.", "error");
            console.error("Error:", error);
        }
    };

    const cancelForm = () => router.push("/list");

    return { pages, formData, isEditMode, fetchPages, fetchPopup, submitForm, cancelForm };
}