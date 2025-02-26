<template>
    <div>
        <h1>{{ popup.title }}</h1>
        <p>{{ popup.description }}</p>
        <p>Page: <a :href="popup.page.url" target="_blank">{{ popup.page.title }}</a></p>
    </div>
</template>

<script>
export default {
    computed: {
        popup() {
            return this.$store.state.popups.find(p => p.id === parseInt(this.$route.params.id));
        }
    }
};
</script>