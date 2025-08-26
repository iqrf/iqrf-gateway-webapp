<template>
	<CIcon
		:class='iconColor'
		:content='iconContent'
		:size='size'
	/>
</template>

<script lang='ts'>
import { cilCheckCircle, cilXCircle } from '@coreui/icons';
import { CIcon } from '@coreui/vue';
import {Component, Model, Prop, Vue} from 'vue-property-decorator';

@Component({
	components: {
		CIcon,
	},
	data: () => ({
		cilCheckCircle,
		cilXCircle,
	}),
})

/**
 * Boolean check icon component
 */
export default class BooleanCheckIcon extends Vue {

	/**
	 * Boolean value model
	 */
	@Model('value', { type: Boolean, default: false })
	readonly value!: boolean;

	/**
	 * Size prop
	 */
	@Prop({
		type: String,
		required: false,
		default: 'lg',
		validator(value: string) {
			return ['sm', 'lg', 'xl'].includes(value);
		},
	})
	readonly size!: string;

	/**
	 * Computes icon color based on value
	 * @return {string} Icon color
	 */
	get iconColor(): string {
		return this.value ? 'text-success' : 'text-danger';
	}

	/**
	 * Computes icon content based on value
	 * @return {string[]} Icon content
	 */
	get iconContent(): string[] {
		return this.value ? cilCheckCircle : cilXCircle;
	}
}
</script>
