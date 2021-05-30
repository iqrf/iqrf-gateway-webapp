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
		<h1>{{ $t('config.daemon.messagings.title') }}</h1>
		<CCard body-wrapper>
			<CListGroup>
				<CListGroupItem
					to='/config/daemon/messagings/mqtt'
				>
					<header class='list-group-item-heading'>
						{{ $t('config.daemon.messagings.mqtt.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.daemon.messagings.mqtt.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					to='/config/daemon/messagings/websocket'
				>
					<header class='list-group-item-heading'>
						{{ $t('config.daemon.messagings.websocket.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.daemon.messagings.websocket.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					to='/config/daemon/messagings/mq'
				>
					<header class='list-group-item-heading'>
						{{ $t('config.daemon.messagings.mq.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.daemon.messagings.mq.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					to='/config/daemon/messagings/udp'
				>
					<header class='list-group-item-heading'>
						{{ $t('config.daemon.messagings.udp.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.daemon.messagings.udp.description') }}
					</p>
				</CListGroupItem>
			</CListGroup>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CCard, CSelect} from '@coreui/vue/src';
import MqttMessagingTable from '../../pages/Config/MqttMessagingTable.vue';
import WebsocketList from '../../pages/Config/WebsocketList.vue';
import MqMessagingTable from '../../pages/Config/MqMessagingTable.vue';
import UdpMessagingTable from '../../pages/Config/UdpMessagingTable.vue';

@Component({
	components: {
		CCard,
		CSelect,
		MqttMessagingTable,
		WebsocketList,
		MqMessagingTable,
		UdpMessagingTable,
	},
	metaInfo: {
		title: 'config.daemon.messagings.title'
	}
})

/**
 * Messagings menu disambiguation component
 */
export default class Messagings extends Vue {

}
</script>
