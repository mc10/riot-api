<?php
	namespace RiotApi;

	require_once('riot-region.php');
	require_once('json-error.php');

	class Api {
		private static $BASE_API_URL = 'http://prod.api.pvp.net/api';
		private static $V1_1_URL = '/lol/{region}/v1.1';
		private static $V2_1_URL = '/{region}/v2.1';

		private $apiKey;
		private $region;

		// API URLs with the correct region bound by bindRegionToUrl
		private $v11Url;
		private $v21Url;

		/**
		 * Initializes the static constants with the right values, as PHP does
		 * not allow static properties to contain expressions.
		 */
		public static function init() {
			self::$V1_1_URL = self::$BASE_API_URL . self::$V1_1_URL;
			self::$V2_1_URL = self::$BASE_API_URL . self::$V2_1_URL;
		}

		public function __construct($apiKey, $region = 'NA') {
			$this->apiKey = $apiKey;

			if (!Region::isRegion($region)) {
				throw new \InvalidArgumentException('Invalid region.');
			}

			$this->region = $region;
			$this->v11Url = $this->bindRegionToUrl(self::$V1_1_URL, $region);
			$this->v21Url = $this->bindRegionToUrl(self::$V2_1_URL, $region);
		}

		public function getChampions($freeToPlay = null) {
			$apiUrl = $this->v11Url . '/champion';
			$params = $this->getDefaultParams();

			if (is_bool($freeToPlay)) {
				$params['freeToPlay'] = ($freeToPlay ? 'true' : 'false');
			}

			$champions = self::getJsonResponse($apiUrl, $params);

			return $champions['champions'];
		}

		public function getRecentGames($summonerId) {
			if (!is_int($summonerId)) {
				throw new \InvalidArgumentException('Summoner ID must be an integer.');
			}

			$apiUrl = $this->v11Url . '/game/by-summoner/' . $summonerId . '/recent';
			$params = $this->getDefaultParams();

			$recentGames = self::getJsonResponse($apiUrl, $params);

			return $recentGames['games'];
		}

		public function getLeagues($summonerId) {
			if (!is_int($summonerId)) {
				throw new \InvalidArgumentException('Summoner ID must be an integer.');
			}

			$apiUrl = $this->v21Url . '/league/by-summoner/' . $summonerId;
			$params = $this->getDefaultParams();

			$leagues = self::getJsonResponse($apiUrl, $params);

			return $leagues;
		}

		public function getStatsSummary($summonerId, $season = null) {
			if (!is_int($summonerId)) {
				throw new \InvalidArgumentException('Summoner ID must be an integer.');
			}

			$apiUrl = $this->v11Url . '/stats/by-summoner/' . $summonerId . '/summary';
			$params = $this->getDefaultParams();

			if (is_int($season)) {
				$params['season'] = 'SEASON' . $season;
			}

			$statsSummary = self::getJsonResponse($apiUrl, $params);

			return $statsSummary['playerStatSummaries'];
		}

		public function getRankedStats($summonerId, $season = null) {
			if (!is_int($summonerId)) {
				throw new \InvalidArgumentException('Summoner ID must be an integer.');
			}

			$apiUrl = $this->v11Url . '/stats/by-summoner/' . $summonerId . '/ranked';
			$params = $this->getDefaultParams();

			if (is_int($season)) {
				$params['season'] = 'SEASON' . $season;
			}

			$rankedStats = self::getJsonResponse($apiUrl, $params);

			return $rankedStats['champions'];
		}

		public function getMasteries($summonerId) {
			if (!is_int($summonerId)) {
				throw new \InvalidArgumentException('Summoner ID must be an integer.');
			}

			$apiUrl = $this->v11Url . '/summoner/' . $summonerId . '/masteries';
			$params = $this->getDefaultParams();

			$masteries = self::getJsonResponse($apiUrl, $params);

			return $masteries['pages'];
		}

		public function getRunes($summonerId) {
			if (!is_int($summonerId)) {
				throw new \InvalidArgumentException('Summoner ID must be an integer.');
			}

			$apiUrl = $this->v11Url . '/summoner/' . $summonerId . '/runes';
			$params = $this->getDefaultParams();

			$runes = self::getJsonResponse($apiUrl, $params);

			return $runes['pages'];
		}

		public function getSummonerByName($summonerName) {
			if (!is_string($summonerName)) {
				throw new \InvalidArgumentException('Summoner name must be a string.');
			}

			$apiUrl = $this->v11Url . '/summoner/by-name/' . $summonerName;
			$params = $this->getDefaultParams();

			$summoner = self::getJsonResponse($apiUrl, $params);

			return $summoner;
		}

		public function getSummonerById($summonerId) {
			if (!is_int($summonerId)) {
				throw new \InvalidArgumentException('Summoner ID must be an integer.');
			}

			$apiUrl = $this->v11Url . '/summoner/' . $summonerId;
			$params = $this->getDefaultParams();

			$summoner = self::getJsonResponse($apiUrl, $params);

			return $summoner;
		}

		public function getSummonerNamesByIds($summonerIdsList) {
			if (!is_array($summonerIdsList)) {
				throw new \InvalidArgumentException('Summoner IDs must be an array.');
			}

			$summonerIds = implode($summonerIdsList, ',');

			$apiUrl = $this->v11Url . '/summoner/' . $summonerIds . '/name';
			$params = $this->getDefaultParams();

			$summonerNames = self::getJsonResponse($apiUrl, $params);

			return $summonerNames['summoners'];
		}

		public function getTeams($summonerId) {
			if (!is_int($summonerId)) {
				throw new \InvalidArgumentException('Summoner ID must be an integer.');
			}

			$apiUrl = $this->v21Url . '/team/by-summoner/' . $summonerId;
			$params = $this->getDefaultParams();

			$teams = self::getJsonResponse($apiUrl, $params);

			return $teams;
		}

		private function getDefaultParams() {
			$params = array();
			$params['api_key'] = $this->apiKey;

			return $params;
		}

		/**
		 * Send an HTTP request, and parse the response as JSON.
		 * @todo   Make the function work with POST requests.
		 * @param  string $url    The URL to send a request to.
		 * @param  array $params  The parameters to send with the request.
		 * @param  string $method The HTTP method to use. Currently, only GET
		 *                        and POST are accepted.
		 * @return array          The response in the form of a parsed array.
		 */
		private static function getJsonResponse($url, $params, $method = 'GET') {
			$httpQuery = http_build_query($params);
			$url .= '?' . $httpQuery;

			echo $url . "\n";

			$ch = curl_init();

			if ($method === 'GET') {
				$curlMethod = CURLOPT_HTTPGET;
			} elseif ($method === 'POST') {
				$curlMethod = CURLOPT_POST;
			} else {
				throw new InvalidArgumentException('Invalid HTTP method; must be either GET or POST.');
			}

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, $curlMethod, true);

			// Get the response as a string
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$jsonResponse = curl_exec($ch);

			curl_close($ch);

			// Use an associative array rather than an object
			$response = json_decode($jsonResponse, true);

			if (json_last_error() != JSON_ERROR_NONE) {
				throw new Exception(json_last_error_msg());
			}

			return $response;
		}

		private static function bindRegionToUrl($url, $region) {
			return str_replace('{region}', strtolower($region), $url);
		}
	}

	Api::init();
