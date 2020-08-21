<template>
	<router-link v-slot='{ href, route, navigate, isActive }' :to='to' active-class='active'>
		<li :class='isActive && "active"'>
			<a :href='href' @click='navigate'>{{ title ? title: $t(route.meta.title) }}</a>
		</li>
	</router-link>
</template>

<script>
export default {
	name: 'NavBarLink',
	props: {
		to: {
			type: String,
			required: true,
		},
		title: {
			type: String,
			default: null,
		},
	},
};
</script>

<style scoped>

</style>
