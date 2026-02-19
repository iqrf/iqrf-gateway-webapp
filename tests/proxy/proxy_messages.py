import json
import time

class ProxyMessages:

	@staticmethod
	def proxy_session_refresh(session_id: int, access_token: str) -> str:
		"""Creates a proxy session refresh message, and serializes it to JSON string."""
		return json.dumps({
			'type': 'proxy_session_refresh',
			'timestamp': int(time.time()),
			'data': {
				'sessionId': session_id,
				'token': access_token,
			},
		})
