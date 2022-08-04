<template>
	<v-list dense>
		<span
			v-for='(item, i) in filteredItems'
			:key='i'
		>
			<v-list-item
				v-if='item.children === undefined'
				:to='item.to'
				:href='item.href'
				:target='item.target'
				router
				exact
				active-class='font-italic font-weight-bold'
			>
				<v-list-item-icon v-if='item.icon !== undefined'>
					<v-icon>{{ item.icon }}</v-icon>
				</v-list-item-icon>
				<v-list-item-content>
					<v-list-item-title>{{ item.title }}</v-list-item-title>
				</v-list-item-content>
			</v-list-item>
			<v-list-group
				v-else
				:group='item.to'
				:prepend-icon='item.icon'
			>
				<template #activator>
					<v-list-item-content>
						<v-list-item-title>{{ item.title }}</v-list-item-title>
					</v-list-item-content>
				</template>
				<span
					v-for='(child, j) in item.children'
					:key='j'
				>
					<v-list-item
						v-if='child.children === undefined'
						:to='child.to'
						:href='child.href'
						:target='child.target'
						router
						exact
					>
						<v-list-item-content>
							<v-list-item-title>{{ child.title }}</v-list-item-title>
						</v-list-item-content>
					</v-list-item>
					<v-list-group
						v-else
						:sub-group='true'
						:group='child.to'
						:prepend-icon='child.icon'
					>
						<template #activator>
							<v-list-item-content>
								<v-list-item-title>{{ child.title }}</v-list-item-title>
							</v-list-item-content>
						</template>
						<v-list-item
							v-for='(grandchild, k) in child.children'
							:key='k'
							:to='grandchild.to'
							:href='grandchild.href'
							:target='grandchild.target'
							router
							exact
						>
							<v-list-item-content>
								<v-list-item-title>{{ grandchild.title }}</v-list-item-title>
							</v-list-item-content>
						</v-list-item>
					</v-list-group>
				</span>
			</v-list-group>
		</span>
	</v-list>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
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
@Component({})
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
