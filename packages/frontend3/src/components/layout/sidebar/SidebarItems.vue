<template>
	<v-list class='text-white'>
		<template v-for='(navItem, idx) in props.items'>
			<SidebarGroup
				v-if='navItem.children !== undefined && navItem.children.length > 0'
				:key='"sidebarGroup" + idx'
				:item='navItem'
			/>
			<SidebarItem
				v-else
				:key='"sidebarItem" + idx'
				:item='navItem'
			/>
		</template>
	</v-list>
</template>

<script lang='ts' setup>
import {SidebarLink} from '@/types/sidebar';
import SidebarGroup from '@/components/layout/sidebar/SidebarGroup.vue';
import SidebarItem from '@/components/layout/sidebar/SidebarItem.vue';
/**
 * Sidebar item component props
 */
interface Props {
	/// Sidebar items to render
	items: Array<SidebarLink>;
}
const props = defineProps<Props>();
</script>
