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
	<v-list class='text-white'>
		<template v-for='navItem in props.items'>
			<SidebarGroup
				v-if='navItem.children !== undefined && navItem.children.length > 0'
				:key='`${navItem.title}_group`'
				:item='navItem'
			/>
			<SidebarItem
				v-else
				:key='`${navItem.title}_item`'
				:item='navItem'
			/>
		</template>
	</v-list>
</template>

<script lang='ts' setup>
import SidebarGroup from '@/components/layout/sidebar/SidebarGroup.vue';
import SidebarItem from '@/components/layout/sidebar/SidebarItem.vue';
import { type SidebarLink } from '@/types/sidebar';
/**
 * Sidebar item component props
 */
interface Props {
	/// Sidebar items to render
	items: SidebarLink[];
}
const props = defineProps<Props>();
</script>
