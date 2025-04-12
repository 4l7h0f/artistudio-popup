<template>
	<div>
		<router-view />
	</div>
</template>

<script>
export default {
	name: "App",
	watch: {
		$route(to) {
			// Update the WordPress admin menu based on the current route
			const menuMap = {
				"/": "artistudio-popup#/",
				"/settings": "artistudio-popup#/settings",
				"/list": "artistudio-popup#/list",
			};

			const menuSlug = menuMap[to.path];
			if (menuSlug) {
				// Highlight the corresponding menu item
				const menuItem = document.querySelector(
					`a[href="admin.php?page=${menuSlug}"]`
				);
				if (menuItem) {
					// Remove active class from all menu items
					document.querySelectorAll("#adminmenu li").forEach((li) => {
						li.classList.remove("current");
					});

					// Add active class to the current menu item
					menuItem.parentElement.classList.add("current");
				}
			}
		},
	},
};
</script>
