import { ref } from 'vue';

export function useNotification() {
    const notification = ref({
        message: "",
        type: "",
    });

    const showNotification = (message, type) => {
        notification.value.message = message;
        notification.value.type = type;
        setTimeout(() => {
            notification.value.message = "";
            notification.value.type = "";
        }, 3000);
    };

    return {
        notification,
        showNotification,
    };
}