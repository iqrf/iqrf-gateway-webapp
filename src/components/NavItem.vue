<template>
	<v-list-item
		:to='item.to'
		:href='item.href'
		:target='item.target'
		router
		exact
		active-class='text-weight-bold'
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
import { NavigationItem } from './SidebarItems.vue';

@Component
export default class NavItem extends Vue {
	@Prop({required: true}) item!: NavigationItem;
}
</script>
