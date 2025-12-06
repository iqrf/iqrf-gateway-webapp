<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->

<template>
	<v-list-item
		:href='item.href'
		:prepend-icon='item.icon'
		:rel='item.target === "_blank" ? "noopener noreferrer" : undefined'
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
import { ref, type Ref, watchEffect } from 'vue';
import { useRoute } from 'vue-router';

import { type SidebarLink } from '@/types/sidebar';

interface Props {
	/// Sidebar item to render
	item: SidebarLink;
}
const componentProps = defineProps<Props>();
const active: Ref<boolean> = ref(false);
const route = useRoute();
watchEffect((): void => {
	if (componentProps.item.group === undefined) {
		active.value = componentProps.item.to === route.path;
	} else if (typeof componentProps.item.group === 'string') {
		active.value = componentProps.item.group === route.path;
	} else {
		active.value = componentProps.item.group.test(route.path);
	}
});
</script>
