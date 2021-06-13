<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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
	<div>
		<h1>{{ $t('gateway.datetime.title') }}</h1>
		<DateTimeZone ref='datetime' />
		<NtpConfig v-if='$store.getters["features/isEnabled"]("ntp")' @refresh-time='refreshTime' />
	</div>
</template>

<script lang='ts'>
import {AxiosError, AxiosResponse} from 'axios';
import {DateTime} from 'luxon';
import {Options, Vue} from 'vue-property-decorator';

import DateTimeZone from '../../components/Gateway/DateTimeZone.vue';
import NtpConfig from '../../components/Gateway/NtpConfig.vue';

import {extendedErrorToast} from '../../helpers/errorToast';
import TimeService from '../../services/TimeService';

import {IOption} from '../../interfaces/coreui';
import {ITime, ITimezone} from '../../interfaces/gatewayTime';

@Options({
	components: {
		DateTimeZone,
		NtpConfig,
	},
	metaInfo: {
		title: 'gateway.datetime.title'
	}
})

/**
 * Gateway time component
 */
export default class GatewayTime extends Vue {

	/**
	 * Refreshes the gateway clock
	 */
	private refreshTime(): void {
		(this.$refs.datetime as DateTimeZone).getTime(true);
	}

}
</script>
