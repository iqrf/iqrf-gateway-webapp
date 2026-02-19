import asyncio
import json
from websockets.asyncio.server import serve

from messages import auth_success

HOST = '0.0.0.0'
PORT = 9500

async def handler(ws):
	async for message in ws:
		print('Incoming message:', message)

		data = json.loads(message)

		# check for auth message
		if (
			'type' in data and data['type'] == 'auth'
			and 'token' in data and data['token'] == 'iqrfgd2;2;wF65PToI51fywQVS+rH9Ss5+ElGx6RWtMLjtS1CKWKs='
		):
			rsp = auth_success()
			await ws.send(rsp)
			continue

		if (
			'mType' in data and data['mType'] == 'iqrfRaw'
			and 'data' in data and isinstance(data['data'], object)
			and 'msgId' in data['data'] and isinstance(data['data']['msgId'], str)
			and data['data']['msgId'] == 'b61e9f93-921b-42ea-b9b5-76a417b93b78'
		):
			rsp = {
				'mType': 'iqrfRaw',
				'data': {
					'msgId': 'b61e9f93-921b-42ea-b9b5-76a417b93b78',
					'rsp': {
						'rData': '00.00.06.83.00.00.00.44',
					},
					'status': 0,
					'insId': 'iqrfgd2',
				},
			}
			await ws.send(json.dumps(rsp))
			continue


async def main():
	async with serve(handler, HOST, PORT) as server:
		print(f'Starting server on {HOST}:{PORT}')
		await server.serve_forever()

if __name__ == "__main__":
	asyncio.run(main())
