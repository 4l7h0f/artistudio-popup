import { ref, computed, onMounted } from 'vue';

export function usePopup() {
    // State
    const showPopup = ref(false);
    const allPopups = ref([]);
    const currentIndex = ref(0);
    const isLoading = ref(true);
    const error = ref(null);

    // Computed
    const currentPopup = computed(() => {
        if (!allPopups.value[currentIndex.value]) return null;

        // Create a deep copy to avoid reactivity issues
        const popup = JSON.parse(JSON.stringify(allPopups.value[currentIndex.value]));

        // Process description for frontend display
        if (popup.description) {
            // Ensure proper line breaks
            popup.description = popup.description.replace(/\n/g, '<br>');
            // Handle WordPress editor specific formatting
            popup.description = popup.description.replace(/<!--\s*wp:.*?-->/g, '');
        }

        return popup;
    });

    const totalPopups = computed(() => allPopups.value.length);
    const popupCounter = computed(() => `${currentIndex.value + 1}/${totalPopups.value}`);

    const fetchPopups = async () => {
        try {
            isLoading.value = true;
            error.value = null;

            const pageId = window.artistudioPopupFrontend?.current_page_id;
            if (!pageId) throw new Error("No page ID found");

            const response = await fetch(
                `${artistudioPopupFrontend.rest_url}popup?status=publish&simple=true&_=${Date.now()}`,
                {
                    headers: {
                        "X-WP-Nonce": artistudioPopupFrontend.nonce,
                    },
                }
            );

            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

            const data = await response.json();
            const popupsArray = Array.isArray(data) ? data : data?.data || [];

            // Sanitize content through WordPress backend
            const sanitizedPopups = await Promise.all(
                popupsArray.map(async popup => {
                    if (popup.description) {
                        const sanitized = await sanitizeContent(popup.description);
                        return { ...popup, description: sanitized };
                    }
                    return popup;
                })
            );

            allPopups.value = sanitizedPopups.filter(popup => popup.page == pageId);

            if (allPopups.value.length) {
                showPopup.value = true;
                currentIndex.value = 0;
                loadEditorStyles();
            }

        } catch (err) {
            error.value = err.message;
            console.error("Popup error:", err);
        } finally {
            isLoading.value = false;
        }
    };

    const sanitizeContent = async (content) => {
        try {
            const response = await fetch(
                `${artistudioPopupFrontend.rest_url}sanitize-content`,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': artistudioPopupFrontend.nonce,
                    },
                    body: JSON.stringify({ content })
                }
            );

            if (!response.ok) throw new Error('Sanitization failed');

            const data = await response.json();
            return data.sanitized;
        } catch (error) {
            console.error('Content sanitization error:', error);
            // Fallback to basic sanitization
            return content.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
        }
    };

    const loadEditorStyles = () => {
        // Check if we're in WordPress environment
        if (!window.artistudioPopupFrontend?.admin_url) {
            console.warn('WordPress admin URL not available');
            return;
        }

        // Check if styles are already loaded
        if (!document.getElementById('wp-editor-css')) {
            const link = document.createElement('link');
            link.id = 'wp-editor-css';
            link.rel = 'stylesheet';
            link.href = `${window.artistudioPopupFrontend.admin_url}/css/editor.min.css`;
            document.head.appendChild(link);
        }

        // Load additional WordPress editor styles
        if (!document.getElementById('dashicons-css')) {
            const dashicons = document.createElement('link');
            dashicons.id = 'dashicons-css';
            dashicons.rel = 'stylesheet';
            dashicons.href = `${window.artistudioPopupFrontend.includes_url}/css/dashicons.min.css`;
            document.head.appendChild(dashicons);
        }
    };

    const closePopup = () => {
        if (currentIndex.value < allPopups.value.length - 1) {
            currentIndex.value++;
        } else {
            showPopup.value = false;
            currentIndex.value = 0;
        }
    };

    onMounted(fetchPopups);

    return {
        showPopup,
        currentPopup,
        currentIndex,
        totalPopups,
        popupCounter,
        closePopup,
        isLoading,
        error,
    };
}