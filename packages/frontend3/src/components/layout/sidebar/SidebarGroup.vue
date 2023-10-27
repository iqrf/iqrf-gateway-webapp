<template>
	<v-list-group
		:prepend-icon='subGroup ? "" : item.icon'
		:subgroup='subGroup'
		:value='item.title'
	>
		<template #activator='{ props }'>
			<v-list-item :title='item.title' density='compact' v-bind='props' />
		</template>
		<div v-for='(navItem, idx) in item.children' :key='idx'>
			<SidebarGroup
				v-if='navItem.children !== undefined && navItem.children.length > 0'
				:item='navItem'
				sub-group
			/>
			<SidebarItem
				v-else
				:item='navItem'
			/>
		</div>
	</v-list-group>
</template>

<script lang='ts' setup>
import SidebarItem from '@/components/layout/sidebar/SidebarItem.vue';
import { type SidebarLink } from '@/types/sidebar';

interface Props {
	/// Sidebar items to render
	item: SidebarLink;
	/// Is subgroup?
	subGroup?: boolean;
}
defineProps<Props>();
</script>
