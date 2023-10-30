<template>
	<v-list-group
		:prepend-icon='item.icon'
		:subgroup='subGroup'
		:value='item.title'
	>
		<template #activator='{ props, isOpen }'>
			<v-list-item
				:title='item.title'
				density='compact'
				v-bind='props'
				:prepend-icon='item.icon ?? ""'
				:append-icon='isOpen ? mdiChevronUp : mdiChevronDown'
			/>
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
import { mdiChevronDown, mdiChevronUp } from '@mdi/js';

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

<style lang='scss' scoped>
.v-list-group {
	.v-list-group__items {
		.v-list-item {
			padding-inline-start: 4em !important;
		}

		.v-list-group {
			.v-list-group__items {
				.v-list-item {
					padding-inline-start: 6em !important;
				}

				.v-list-group {
					.v-list-group__items {
						.v-list-item {
							padding-inline-start: 8em !important;
						}
					}
				}
			}
		}
	}
}
</style>
