<template>
	<v-list-item
		:href='item.href'
		:prepend-icon='item.icon'
		:target='item.target'
		:to='item.to'
		:active='active'
		density='compact'
		exact
		router
	>
		<v-list-item-title>{{ item.title }}</v-list-item-title>
	</v-list-item>
</template>

<script lang='ts' setup>
import { type Ref, ref, watchEffect } from 'vue';
import { useRoute } from 'vue-router';

import { type SidebarLink } from '@/types/sidebar';

interface Props {
	/// Sidebar item to render
	item: SidebarLink;
}
const props = defineProps<Props>();
const active: Ref<boolean> = ref(false);
const route = useRoute();
watchEffect((): void => {
	if (props.item.group === undefined) {
		active.value = props.item.to === route.path;
	} else if (typeof props.item.group === 'string') {
		active.value = props.item.group === route.path;
	} else {
		active.value = props.item.group.test(route.path);
	}
});
</script>
