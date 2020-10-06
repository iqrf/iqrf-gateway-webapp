import FeatureService from '../../services/FeatureService';
import {AxiosResponse} from 'axios';

const state = {
	features: {},
};

const actions = {
	// @ts-ignore
	fetch({commit}) {
		return FeatureService.fetchAll()
			.then((response: AxiosResponse) => {
				commit('SET', response.data);
			});
	},
};

const getters = {
	isEnabled: (state: any) => (name: string) => {
		try {
			return state.features[name].enabled;
		} catch (e) {
			return undefined;
		}
	},
	configuration: (state: any) => (name: string) => {
		try {
			return state.features[name];
		} catch (e) {
			return undefined;
		}
	},
};

const mutations = {
	SET(state: any, features: any) {
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
