import {type VForm} from 'vuetify/components';

/**
 * Validate Vuetify form
 * @param {VForm|null} form Vuetify form
 * @return {Promise<boolean>} Validation result
 */
export const validateForm = async (form: typeof VForm | null): Promise<boolean> => {
	if (form === null) {
		return false;
	}
	const result = await form.validate();
	return result.valid;
};
