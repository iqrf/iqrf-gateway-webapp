import FeatureService, {Feature, Features} from '../../services/FeatureService';
import {ActionTree, GetterTree, MutationTree} from 'vuex';

/**
 * Feature state
 */
interface FeatureState {

	/**
	 * Features
	 */
	features: Features,

}

const state: FeatureState = {
	features: {},
};

const actions: ActionTree<FeatureState, any> = {
	fetch({commit}) {
		return FeatureService.fetchAll()
			.then((features: Features) => {
				commit('SET', features);
			});
	},
};

const getters: GetterTree<FeatureState, any> = {
	isEnabled: (state: FeatureState) => (name: string): boolean|undefined => {
		try {
			return state.features[name].enabled;
		} catch (e) {
			return undefined;
		}
	},
	configuration: (state: FeatureState) => (name: string): Feature|undefined => {
		try {
			return state.features[name];
		} catch (e) {
			return undefined;
		}
	},
};

const mutations: MutationTree<FeatureState> = {
	SET(state: FeatureState, features: Features) {
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
