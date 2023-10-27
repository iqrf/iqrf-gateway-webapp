<template>
	<v-list
		class='py-0'
		variant='elevated'
		elevation='1'
		rounded
	>
		<v-list-item
			v-for='link in filteredLinks'
			:key='link.title'
			lines='two'
			:to='link.to'
			:href='link.href'
			:target='link.target'
		>
			<v-list-item-title>{{ link.title }}</v-list-item-title>
			<v-list-item-subtitle>{{ link.description }}</v-list-item-subtitle>
		</v-list-item>
	</v-list>
</template>

<script lang='ts' setup>
import { computed, type ComputedRef, type PropType } from 'vue';

import { useFeatureStore } from '@/store/features';
import { useUserStore } from '@/store/user';
import { type DisambiguationLink } from '@/types/disambiguation';

const userStore = useUserStore();
const featureStore = useFeatureStore();
const props = defineProps({
	links: {
		type: Array as PropType<DisambiguationLink[]>,
		default: () => [],
		required: true,
	},
});

const filteredLinks: ComputedRef<DisambiguationLink[]> = computed(() => {
	return props.links.filter((link: DisambiguationLink) => {
		if (link.roles !== undefined && !link.roles.includes(userStore.getRole!)) {
			return false;
		}
		return !(link.feature !== undefined && !featureStore.isEnabled(link.feature));
	});
});
</script>
