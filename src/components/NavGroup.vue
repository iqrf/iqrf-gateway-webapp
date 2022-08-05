<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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
		v-if='!subGroup'
		:group='item.to'
	>
		<template #prependIcon>
			<v-icon v-if='item.icon !== undefined'>
				{{ item.icon }}
			</v-icon>
		</template>
		<template v-if='subGroup' #appendIcon>
			<v-icon>mdi-chevron-down</v-icon>
		</template>
		<template #activator>
			<v-list-item-title>{{ item.title }}</v-list-item-title>
		</template>
		<template v-for='(navItem, idx) in item.children'>
			<NavGroup
				v-if='navItem.children !== undefined && navItem.children.length > 0'
				:key='idx'
				:item='navItem'
				:sub-group='true'
			/>
			<NavItem
				v-else
				:key='idx'
				:item='navItem'
			/>
		</template>
	</v-list-group>
	<NavListGroup
		v-else
		:sub-group='subGroup'
		:to='item.to'
		:href='item.href'
		:target='item.href'
		router
		exact
	>
		<template #prependIcon>
			<v-icon v-if='item.icon !== undefined'>
				{{ item.icon }}
			</v-icon>
		</template>
		<template v-if='subGroup' #appendIcon>
			<v-icon>mdi-chevron-down</v-icon>
		</template>
		<template #activator>
			<v-list-item-title>{{ item.title }}</v-list-item-title>
		</template>
		<template v-for='(navItem, idx) in item.children'>
			<NavGroup
				v-if='navItem.children !== undefined && navItem.children.length > 0'
				:key='idx'
				:item='navItem'
				:sub-group='true'
			/>
			<NavItem
				v-else
				:key='idx'
				:item='navItem'
			/>
		</template>
	</NavListGroup>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import NavItem from './NavItem.vue';
import NavListGroup from '@/components/NavListGroup';

import {NavigationItem} from './SidebarItems.vue';

/**
 * Navigation menu item group component
 */
@Component({
	components: {
		NavListGroup,
		NavItem,
	},
})
export default class NavGroup extends Vue {
	/**
	 * @property {NavigationItem} item Navigation item
	 */
	@Prop({required: true}) item!: NavigationItem;

	/**
	 * @property {boolean} subGroup Indicates whether item is a top level group or not
	 */
	@Prop({required: false, type: Boolean, default: false}) subGroup!: boolean;
}
</script>
