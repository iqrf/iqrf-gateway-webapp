<template>
	<router-link v-slot='{ href, route, navigate }' :to='to'>
		<a :href='href' class='list-group-item' @click='navigate'>
			<header class='list-group-item-heading'>
				{{ title ? title : $t(route.meta.title) }}
			</header>
			<p class='list-group-item-text'>
				{{ description ? description : $t(route.meta.description) }}
			</p>
		</a>
	</router-link>
</template>

<script>

export default {
	name: 'DisambiguationLink',
	props: {
		to: {
			type: String,
			required: true,
		},
		title: {
			type: String,
			default: null,
		},
		description: {
			type: String,
			default: null,
		},
	},
};
</script>

<style scoped>

</style>
