<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
