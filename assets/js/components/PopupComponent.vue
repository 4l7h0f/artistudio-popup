<template>
    <div class="popup">
        <!-- <h2>{{ title }}</h2> -->
        <p>{{ description }}</p>
    </div>
</template>

<script>
export default {
    props: {
        title: String,
        description: String,
    },
};
</script>

<style scoped>
.popup {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 20px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}
</style>