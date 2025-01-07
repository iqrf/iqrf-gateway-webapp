<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
	<div class='d-inline'>
		{{ $t('gateway.info.usages.used') }}
		{{ usage.usage.replace('%', ' %') }}
		({{ usage.used }} / {{ usage.size }})
		<v-progress-linear
			v-model='usage.usage'
			:color='color'
			height='1em'
			rounded
		/>
	</div>
</template>

<script lang='ts'>
import {UsageBase} from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';
import {Component, Prop, Vue} from 'vue-property-decorator';

/**
 * Resource usage component for gateway information
 */
@Component({})
export default class ResourceUsage extends Vue {
	/**
	 * @property {UsageBase} usage File system usage
	 */
	@Prop({ required: true }) usage!: UsageBase;

	/**
	 * Returns CSS classes for the progress bar
	 */
	get color(): string {
		const usage = Number.parseFloat(this.usage.usage.replace('%', ''));
		if (usage >= 90) {
			return 'danger';
		} else if (usage >= 80) {
			return 'warning';
		} else {
			return 'success';
		}
	}
}
</script>

<style lang='scss'>
.table-striped tbody tr:nth-of-type(2n+1) .progress {
	background-color: white;
}
</style>
