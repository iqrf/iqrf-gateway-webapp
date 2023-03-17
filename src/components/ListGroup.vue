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
		:sub-group='subGroup'
		:group='item.group ?? item.to'
		:prepend-icon='subGroup ? "" : item.icon'
	>
		<template v-if='subGroup' #appendIcon>
			<v-icon>mdi-chevron-down</v-icon>
		</template>
		<template #activator>
			<v-list-item-title>{{ item.title }}</v-list-item-title>
		</template>
		<div v-for='(navItem, idx) in item.children' :key='idx'>
			<ListGroup
				v-if='navItem.children !== undefined && navItem.children.length > 0'
				:item='navItem'
				sub-group
			/>
			<ListItem
				v-else
				:item='navItem'
			/>
		</div>
	</v-list-group>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import ListItem from './ListItem.vue';

import {NavigationItem} from './SidebarItems.vue';

/**
 * Navigation menu item group component
 */
@Component({
	name: 'ListGroup',
	components: {
		ListItem,
	},
})
export default class ListGroup extends Vue {
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
