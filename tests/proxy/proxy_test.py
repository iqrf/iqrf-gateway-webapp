import json
import pytest
from websockets import connect, ConnectionClosed

from assertions import assert_prop_type_value, assert_prop_type
from proxy_messages import ProxyMessages

PROXY_URL = 'ws://proxy:9000'

@pytest.mark.asyncio
async def test_proxy_connect_no_token():
	"""Connecting without a token should result in connection closed with policy error."""
	async with connect(PROXY_URL) as ws:
		msg = await ws.recv()
		data = json.loads(msg)

		assert_prop_type_value(data, 'type', str, 'proxy_auth_failed')
		assert_prop_type(data, 'timestamp', int)
		assert_prop_type(data, 'data', object)
		assert_prop_type_value(data['data'], 'code', int, 0)

		with pytest.raises(ConnectionClosed) as exc:
			await ws.recv()

		exception = exc.value
		assert exception.rcvd.code == 1008

@pytest.mark.asyncio
async def test_proxy_connect_invalid_token_format():
	"""Connecting with token of invalid format should result in connection closed with policy error."""
	async with connect(f'{PROXY_URL}?token=invalid') as ws:
		msg = await ws.recv()
		data = json.loads(msg)

		assert_prop_type_value(data, 'type', str, 'proxy_auth_failed')
		assert_prop_type(data, 'timestamp', int)
		assert_prop_type(data, 'data', object)
		assert_prop_type_value(data['data'], 'code', int, 1)

		with pytest.raises(ConnectionClosed) as exc:
			await ws.recv()

		exception = exc.value
		assert exception.rcvd.code == 1008

@pytest.mark.asyncio
async def test_proxy_connect_invalid_token(invalid_token):
	"""Connecting with not identifying a user should result in connection closed with policy error."""
	async with connect(f'{PROXY_URL}?token={invalid_token}') as ws:
		msg = await ws.recv()
		data = json.loads(msg)

		assert_prop_type_value(data, 'type', str, 'proxy_auth_failed')
		assert_prop_type(data, 'timestamp', int)
		assert_prop_type(data, 'data', object)
		assert_prop_type_value(data['data'], 'code', int, 1)

		with pytest.raises(ConnectionClosed) as exc:
			await ws.recv()

		exception = exc.value
		assert exception.rcvd.code == 1008

@pytest.mark.asyncio
async def test_proxy_connect_success(api):
	"""Connecting with a valid token should result in proxy authentication success."""
	access_token = await api.sign_in()

	async with connect(f'{PROXY_URL}?token={access_token}') as ws:
		msg = await ws.recv()
		data = json.loads(msg)

		# process expected proxy auth success
		assert_prop_type_value(data, 'type', str, 'proxy_auth_success')
		assert_prop_type(data, 'timestamp', int)
		assert_prop_type(data, 'data', object)
		assert_prop_type(data['data'], 'sessionId', int)

		msg = await ws.recv()
		data = json.loads(msg)

		# process upstream ready message
		assert_prop_type_value(data, 'type', str, 'upstream_ready')
		assert_prop_type(data, 'timestamp', int)
		assert_prop_type(data, 'data', object)
		assert_prop_type(data['data'], 'expiration', int)

@pytest.mark.asyncio
async def test_proxy_receive_invalid_message(api):
	"""Sending message that is not a JSON string is considered invalid and should result in proxy message invalid response."""
	access_token = await api.sign_in()

	async with connect(f'{PROXY_URL}?token={access_token}') as ws:
		msg = json.loads(await ws.recv())

		# process expected proxy auth success
		assert_prop_type_value(msg, 'type', str, 'proxy_auth_success')
		assert_prop_type(msg, 'timestamp', int)
		assert_prop_type(msg, 'data', object)
		assert_prop_type(msg['data'], 'sessionId', int)

		msg = json.loads(await ws.recv())

		# process upstream ready message
		assert_prop_type_value(msg, 'type', str, 'upstream_ready')
		assert_prop_type(msg, 'timestamp', int)
		assert_prop_type(msg, 'data', object)
		assert_prop_type(msg['data'], 'expiration', int)

		rq = '{"type}'
		await ws.send(rq)
		msg = json.loads(await ws.recv())

		# process proxy message invalid
		assert_prop_type_value(msg, 'type', str, 'proxy_message_invalid')
		assert_prop_type(msg, 'timestamp', int)
		assert_prop_type(msg, 'data', object)
		assert_prop_type_value(msg['data'], 'message', str, rq)
		assert_prop_type(msg['data'], 'error', str)

@pytest.mark.asyncio
async def test_proxy_session_refresh_invalid_format_token(api):
	"""Sending a session refresh message with invalid token format should result in a proxy session refresh failed response."""
	access_token = await api.sign_in()

	async with connect(f'{PROXY_URL}?token={access_token}') as ws:
		msg = json.loads(await ws.recv())

		# process expected proxy auth success
		assert_prop_type_value(msg, 'type', str, 'proxy_auth_success')
		assert_prop_type(msg, 'timestamp', int)
		assert_prop_type(msg, 'data', object)
		assert_prop_type(msg['data'], 'sessionId', int)

		session_id = msg['data']['sessionId']

		msg = json.loads(await ws.recv())

		# process upstream ready message
		assert_prop_type_value(msg, 'type', str, 'upstream_ready')
		assert_prop_type(msg, 'timestamp', int)
		assert_prop_type(msg, 'data', object)
		assert_prop_type(msg['data'], 'expiration', int)

		await ws.send(
			ProxyMessages.proxy_session_refresh(
				session_id=session_id,
				access_token='{abcd',
			)
		)
		msg = json.loads(await ws.recv())

		# process proxy session refresh invalid
		assert_prop_type_value(msg, 'type', str, 'proxy_session_refresh_failed')
		assert_prop_type(msg, 'timestamp', int)

@pytest.mark.asyncio
async def test_proxy_session_refresh_different_user(api, invalid_token):
	"""Sending a session refresh message with a token identifying another user should result in a proxy session refresh failed response."""
	access_token = await api.sign_in()

	async with connect(f'{PROXY_URL}?token={access_token}') as ws:
		msg = json.loads(await ws.recv())

		# process expected proxy auth success
		assert_prop_type_value(msg, 'type', str, 'proxy_auth_success')
		assert_prop_type(msg, 'timestamp', int)
		assert_prop_type(msg, 'data', object)
		assert_prop_type(msg['data'], 'sessionId', int)

		session_id = msg['data']['sessionId']

		msg = json.loads(await ws.recv())

		# process upstream ready message
		assert_prop_type_value(msg, 'type', str, 'upstream_ready')
		assert_prop_type(msg, 'timestamp', int)
		assert_prop_type(msg, 'data', object)
		assert_prop_type(msg['data'], 'expiration', int)

		await ws.send(
			ProxyMessages.proxy_session_refresh(
				session_id=session_id,
				access_token=invalid_token,
			)
		)
		msg = json.loads(await ws.recv())

		# process proxy session refresh invalid
		assert_prop_type_value(msg, 'type', str, 'proxy_session_refresh_failed')
		assert_prop_type(msg, 'timestamp', int)

@pytest.mark.asyncio
async def test_proxy_session_refresh_session_id_mismatch(api):
	"""Sending a session refresh message with mismatched session ID should result in a proxy session refresh failed response."""
	access_token = await api.sign_in()

	async with connect(f'{PROXY_URL}?token={access_token}') as ws:
		msg = json.loads(await ws.recv())

		# process expected proxy auth success
		assert_prop_type_value(msg, 'type', str, 'proxy_auth_success')
		assert_prop_type(msg, 'timestamp', int)
		assert_prop_type(msg, 'data', object)
		assert_prop_type(msg['data'], 'sessionId', int)

		msg = json.loads(await ws.recv())

		# process upstream ready message
		assert_prop_type_value(msg, 'type', str, 'upstream_ready')
		assert_prop_type(msg, 'timestamp', int)
		assert_prop_type(msg, 'data', object)
		assert_prop_type(msg['data'], 'expiration', int)

		new_access_token = await api.refresh_session(access_token=access_token)

		await ws.send(
			ProxyMessages.proxy_session_refresh(
				session_id=0,
				access_token=new_access_token,
			)
		)
		msg = json.loads(await ws.recv())

		# process proxy session refresh invalid
		assert_prop_type_value(msg, 'type', str, 'proxy_session_refresh_failed')
		assert_prop_type(msg, 'timestamp', int)

@pytest.mark.asyncio
async def test_proxy_session_refresh_session_success(api):
	"""Sending a session refresh message with valid token and matching session ID should result in a proxy session refresh sucess response."""
	access_token = await api.sign_in()

	async with connect(f'{PROXY_URL}?token={access_token}') as ws:
		msg = json.loads(await ws.recv())

		# process expected proxy auth success
		assert_prop_type_value(msg, 'type', str, 'proxy_auth_success')
		assert_prop_type(msg, 'timestamp', int)
		assert_prop_type(msg, 'data', object)
		assert_prop_type(msg['data'], 'sessionId', int)

		session_id = msg['data']['sessionId']

		msg = json.loads(await ws.recv())

		# process upstream ready message
		assert_prop_type_value(msg, 'type', str, 'upstream_ready')
		assert_prop_type(msg, 'timestamp', int)
		assert_prop_type(msg, 'data', object)
		assert_prop_type(msg['data'], 'expiration', int)

		new_access_token = await api.refresh_session(access_token=access_token)

		await ws.send(
			ProxyMessages.proxy_session_refresh(
				session_id=session_id,
				access_token=new_access_token,
			)
		)
		msg = json.loads(await ws.recv())

		# process proxy session refresh invalid
		assert_prop_type_value(msg, 'type', str, 'proxy_session_refresh_success')
		assert_prop_type(msg, 'timestamp', int)

@pytest.mark.asyncio
async def test_proxy_session_not_daemon_api_message(api):
	"""Sending a message that is not a valid Daemon API message should result in upstream request invalid response."""
	access_token = await api.sign_in()

	async with connect(f'{PROXY_URL}?token={access_token}') as ws:
		msg = json.loads(await ws.recv())

		# process expected proxy auth success
		assert_prop_type_value(msg, 'type', str, 'proxy_auth_success')
		assert_prop_type(msg, 'timestamp', int)
		assert_prop_type(msg, 'data', object)
		assert_prop_type(msg['data'], 'sessionId', int)

		session_id = msg['data']['sessionId']

		msg = json.loads(await ws.recv())

		# process upstream ready message
		assert_prop_type_value(msg, 'type', str, 'upstream_ready')
		assert_prop_type(msg, 'timestamp', int)
		assert_prop_type(msg, 'data', object)
		assert_prop_type(msg['data'], 'expiration', int)

		rq = '{"testkey":"testval"}'
		await ws.send(rq)
		msg = json.loads(await ws.recv())

		# process proxy session refresh invalid
		assert_prop_type_value(msg, 'type', str, 'upstream_request_invalid')
		assert_prop_type(msg, 'timestamp', int)
		assert_prop_type_value(msg, 'data', str, rq)

@pytest.mark.asyncio
async def test_proxy_session_upstream_response(api):
	"""Sending a valid Daemon API message should result in upstream response."""
	access_token = await api.sign_in()

	async with connect(f'{PROXY_URL}?token={access_token}') as ws:
		msg = json.loads(await ws.recv())

		# process expected proxy auth success
		assert_prop_type_value(msg, 'type', str, 'proxy_auth_success')
		assert_prop_type(msg, 'timestamp', int)
		assert_prop_type(msg, 'data', object)
		assert_prop_type(msg['data'], 'sessionId', int)

		session_id = msg['data']['sessionId']

		msg = json.loads(await ws.recv())

		# process upstream ready message
		assert_prop_type_value(msg, 'type', str, 'upstream_ready')
		assert_prop_type(msg, 'timestamp', int)
		assert_prop_type(msg, 'data', object)
		assert_prop_type(msg['data'], 'expiration', int)

		rq = {
			'mType': 'iqrfRaw',
			'data': {
				'msgId': 'b61e9f93-921b-42ea-b9b5-76a417b93b78',
				'timeout': 1000,
				'req': {
					'rData': '00.00.06.03.FF.FF'
				},
				'returnVerbose': True,
			}
		}
		await ws.send(json.dumps(rq))
		msg = json.loads(await ws.recv())

		# process proxy session refresh invalid
		assert_prop_type_value(msg, 'type', str, 'upstream_response')
		assert_prop_type(msg, 'timestamp', int)
		assert_prop_type_value(
			msg,
			'data',
			object,
			{
				'mType': 'iqrfRaw',
				'data': {
					'msgId': 'b61e9f93-921b-42ea-b9b5-76a417b93b78',
					'rsp': {
						'rData': '00.00.06.83.00.00.00.44',
					},
					'status': 0,
					'insId': 'iqrfgd2',
				},
			},
		)
