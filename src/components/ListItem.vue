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
	<v-list-item
		:to='item.to'
		:href='item.href'
		:target='item.target'
		router
		exact
		active-class='selected'
	>
		<v-list-item-icon v-if='item.icon !== undefined'>
			<v-icon>
				{{ item.icon }}
			</v-icon>
		</v-list-item-icon>
		<v-list-item-content>
			<v-list-item-title>{{ item.title }}</v-list-item-title>
		</v-list-item-content>
	</v-list-item>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';

import {NavigationItem} from './SidebarItems.vue';

/**
 * Navigation menu item component
 */
@Component
export default class ListItem extends Vue {
	/**
	 * @property {NavigationItem} item Navigation item
	 */
	@Prop({required: true}) item!: NavigationItem;
}
</script>
