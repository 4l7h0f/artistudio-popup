<template>
    <div>
        <h1>Popups</h1>
        <ul>
            <li v-for="popup in popups" :key="popup.id">
                <router-link :to="`/popup/${popup.id}`">{{ popup.title }}</router-link>
                <p>Page: <a :href="popup.page.url" target="_blank">{{ popup.page.title }}</a></p>
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    computed: {
        popups() {
            return this.$store.state.popups;
        }
    }
};
</script>