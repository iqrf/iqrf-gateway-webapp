import json
import pytest
from websockets import connect, ConnectionClosed
from assertions import assert_prop_type_value, assert_prop_type

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
