import {GetterTree, MutationTree} from 'vuex';

/**
 * Sidebar state
 */
interface SidebarState {

	/**
	 * Sidebar show state
	 */
	show: boolean|string;

	/**
	 * Is the sidebar minimized?
	 */
	minimize: boolean;

}

const state: SidebarState = {
	show: 'responsive',
	minimize: false,
};

const getters: GetterTree<SidebarState, any> = {
	isMinimized(state: SidebarState): boolean {
		return state.minimize;
	},
};

const mutations: MutationTree<SidebarState> = {
	toggleSidebarDesktop (state: SidebarState) {
		const sidebarOpened = [true, 'responsive'].includes(state.show);
		state.show = sidebarOpened ? false : 'responsive';
	},
	toggleSidebarMobile (state: SidebarState) {
		const sidebarClosed = [false, 'responsive'].includes(state.show);
		state.show = sidebarClosed ? true : 'responsive';
	},
	set (state: SidebarState, [variable, value]) {
		state[variable] = value;
	}
};

export default {
	namespaced: true,
	state,
	getters,
	mutations
};
