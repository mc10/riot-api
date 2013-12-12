from riot_region import Region

class Api:
    BASE_API_URL = "http://prod.api.pvp.net/api"
    V1_1_URL = "/lol/{region}/v1.1"
    V2_1_URL = "/{region}/v2.1"

    def __init__(self, api_key, region="NA"):
        self._api_key = api_key

        if not Region.is_region(region):
            raise ValueError("Invalid region.")

        self._region = region
        self._v1_1_url = Api._bind_region_to_url(Api.V1_1_URL, region)
        self._v2_1_url = Api._bind_region_to_url(Api.V2_1_URL, region)

    @classmethod
    def _bind_region_to_url(cls, url, region):
        return url.replace('{region}', region.lower());

