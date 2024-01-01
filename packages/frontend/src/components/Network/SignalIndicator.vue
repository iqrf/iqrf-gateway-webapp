<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->
<template>
	<v-progress-linear
		v-if='signal !== undefined'
		:value='signal'
		:color='color'
		height='1.5em'
		rounded
	>
		<span>{{ Math.ceil(signal) }}%</span>
	</v-progress-linear>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';

/**
 * Signal strength indicator
 */
@Component
export default class SignalIndicator extends Vue {

	/**
	 * @property {number} signal Signal strength
	 */
	@Prop({type: Number, required: true}) signal!: number;

	/**
	 * @property {string} color Returns color for progress bar depending on signal strength
	 */
	get color(): string {
		if (this.signal >= 67) {
			return 'success';
		} else if (this.signal >= 34) {
			return 'warning';
		} else {
			return 'error';
		}
	}

}
</script>

<style lang="scss" scoped>
span {
	color: white;
}
</style>
