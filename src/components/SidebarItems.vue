<template>
	<v-list dense>
		<template v-for='(navItem, idx) in filteredItems'>
			<NavGroup v-if='navItem.children !== undefined && navItem.children.length > 0' :key='idx' :item='navItem' />
			<NavItem v-else :key='idx' :item='navItem' />
		</template>
	</v-list>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import NavGroup from './NavGroup.vue';
import NavItem from './NavItem.vue';

import {LinkTarget} from '@/helpers/DisambiguationHelper';
import {UserRoleIndex} from '@/services/AuthenticationService';

export interface NavigationItem {
	icon?: string;
	title: string;
	children?: Array<NavigationItem>;
	to?: string;
	href?: string;
	target?: LinkTarget;
	role?: UserRoleIndex|Array<UserRoleIndex>;
	feature?: string;
}

/**
 * Sidebar items
 */
@Component({
	components: {
		NavGroup,
		NavItem,
	},
})
export default class SidebarItems extends Vue {

	/**
	 * The items to display in the sidebar.
	 */
	@Prop({required: true})
	private items!: Array<NavigationItem>;

	/**
	 * The filtered items to display in the sidebar.
	 * @returns {Array<NavigationItem>} The filtered items.
	 */
	get filteredItems(): Array<NavigationItem> {
		return this.items.filter((item: NavigationItem) => this.filter(item));
	}

	/**
	 * Filters out items that the user doesn't have access to.
	 * @param {NavigationItem} item The item to filter.
	 * @return {boolean} True if the item should be shown, false otherwise.
	 */
	private filter(item: NavigationItem): boolean {
		const role: UserRoleIndex = this.$store.getters['user/getRoleIndex'];
		if (item.children !== undefined) {
			item.children = item.children.filter((child: NavigationItem) => this.filter(child));
		}
		if (item.role !== undefined && ((Array.isArray(item.role) && item.role.indexOf(role) === -1) || role > item.role)) {
			return false;
		}
		return !(item.feature !== undefined && !this.$store.getters['features/isEnabled'](item.feature));
	}

}
</script>
