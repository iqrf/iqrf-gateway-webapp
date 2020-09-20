import {MutationTree} from 'vuex';

const state = {
	show: 'responsive',
	minimize: false,
};

const mutations: MutationTree<any> = {
	toggleSidebarDesktop (state) {
		const sidebarOpened = [true, 'responsive'].includes(state.show);
		state.show = sidebarOpened ? false : 'responsive';
	},
	toggleSidebarMobile (state) {
		const sidebarClosed = [false, 'responsive'].includes(state.show);
		state.show = sidebarClosed ? true : 'responsive';
	},
	set (state, [variable, value]) {
		state[variable] = value;
	}
};

export default {
	namespaced: true,
	state,
	mutations
};
