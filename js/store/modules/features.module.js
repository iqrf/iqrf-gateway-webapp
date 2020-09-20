import FeatureService from '../../services/FeatureService';

const state = {
	features: {},
};

const actions = {
	fetch({commit}) {
		return FeatureService.fetchAll()
			.then((response) => {
				commit('SET', response.data);
			});
	},
};

const getters = {
	isEnabled: (state) => (name) => {
		try {
			return state.features[name].enabled;
		} catch (e) {
			return undefined;
		}
	},
	configuration: (state) => (name) => {
		try {
			return state.features[name];
		} catch (e) {
			return undefined;
		}
	},
};

const mutations = {
	SET(state, features) {
		state.features = features;
	}
};

export default {
	namespaced: true,
	state,
	actions,
	getters,
	mutations,
};
