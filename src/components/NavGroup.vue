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

@Component({
	components: {
		NavListGroup,
		NavItem,
	},
})
export default class NavGroup extends Vue {
	@Prop({required: true}) item!: NavigationItem;

	@Prop({required: false, type: Boolean, default: false}) subGroup!: boolean;
}
</script>
