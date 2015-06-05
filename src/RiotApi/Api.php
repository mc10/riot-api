<?php
	namespace RiotApi;

	require 'Autoloader.php';
	require 'JsonError.php';

	class Api {
		const BASE_URL = 'https://{region}.api.pvp.net';
		const BASE_API_PATH = '/api/lol{static-data}/{region}/v{version}{operation}';
		const BASE_OBSERVER_PATH = '/observer-mode/rest';

		private static $API_URLS = array(
			'champion' => '',
			'game' => '',
			'league' => '',
			'match' => '',
			'matchhistory' => '',
			'staticData' => '',
			'stats' => '',
			'summoner' => '',
			'team' => ''
		);

		public static $OBSERVER_URLS = array(
			'currentGame' => '/consumer/getSpectatorGameInfo/{platformId}/{summonerId}',
			'featuredGames' => '/featured'
		);

		private $apiKey;
		private $region;

		/**
		 * Initializes the static constants with the right values, as PHP does
		 * not allow static properties to contain expressions.
		 */
		public static function init() {
			foreach (self::$API_URLS as $operation => &$url) {
				$url = self::buildApiUrl($operation);
			}

			foreach (self::$OBSERVER_URLS as $operation => &$url) {
				$url = self::buildObserverUrl($url, $operation);
			}
		}

		/**
		 * Creates an instance of the API.
		 * @param string $apiKey The API key that Riot provides.
		 * @param string $region The region used for API calls. Defaults to NA.
		 */
		public function __construct($apiKey, $region = 'NA') {
			$this->apiKey = $apiKey;

			if (!Region::isRegion($region)) {
				throw new \InvalidArgumentException('Invalid region.');
			}

			$this->region = $region;
		}

		/**
		 * Set the region currently used by the API.
		 * @param string $region The new region.
		 */
		public function setRegion($region) {
			if (!Region::isRegion($region)) {
				throw new \InvalidArgumentException('Invalid region.');
			}

			$this->region = $region;
		}

		public function getChampions($freeToPlay = null) {
			$apiUrl = self::$API_URLS['champion'];
			$params = array();

			if (is_bool($freeToPlay)) {
				$params['freeToPlay'] = ($freeToPlay ? 'true' : 'false');
			}

			$champions = $this->sendApiRequest($apiUrl, $params);

			return $champions['champions'];
		}

		public function getRecentGames($summonerId) {
			if (!is_int($summonerId)) {
				throw new \InvalidArgumentException('Summoner ID must be an integer.');
			}

			$apiUrl = self::$API_URLS['game'] . '/by-summoner/' . $summonerId . '/recent';
			$recentGames = $this->sendApiRequest($apiUrl);

			return $recentGames['games'];
		}

		public function getLeaguesBySummoner($summonerIds) {
			$MAX_IDS = 10;

			if (!is_array($summonerIds) && !is_int($summonerIds)) {
				throw new \InvalidArgumentException('Summoner IDs must be an '
					. 'integer or a list of integers.');
			}

			if (!is_array($summonerIds)) {
				$summonerIds = array($summonerIds);
			}

			if (count($summonerIds) > $MAX_IDS) {
				throw new \InvalidArgumentException('The list of summoner IDs '
					. 'cannot exceed ' . $MAX_IDS . ' entries.');
			}

			$apiUrl = self::$API_URLS['league'] . '/by-summoner/' . implode(',', $summonerIds);
			$leagues = $this->sendApiRequest($apiUrl);

			return $leagues;
		}

		public function getLeagueEntriesBySummoner($summonerIds) {
			$MAX_IDS = 10;

			if (!is_array($summonerIds) && !is_int($summonerIds)) {
				throw new \InvalidArgumentException('Summoner IDs must be an '
					. 'integer or a list of integers.');
			}

			if (!is_array($summonerIds)) {
				$summonerIds = array($summonerIds);
			}

			if (count($summonerIds) > $MAX_IDS) {
				throw new \InvalidArgumentException('The list of summoner IDs '
					. 'cannot exceed ' . $MAX_IDS . ' entries.');
			}

			$apiUrl = self::$API_URLS['league'] . '/by-summoner/' . implode(',', $summonerIds) . '/entry';
			$leagues = $this->sendApiRequest($apiUrl);

			return $leagues;
		}

		public function getStatsSummary($summonerId, $season = null) {
			if (!is_int($summonerId)) {
				throw new \InvalidArgumentException('Summoner ID must be an integer.');
			}

			$apiUrl = self::$API_URLS['stats'] . '/by-summoner/' . $summonerId . '/summary';
			$params = array();

			if (is_int($season)) {
				$params['season'] = 'SEASON' . $season;
			}

			$statsSummary = $this->sendApiRequest($apiUrl, $params);

			return $statsSummary['playerStatSummaries'];
		}

		public function getRankedStats($summonerId, $season = null) {
			if (!is_int($summonerId)) {
				throw new \InvalidArgumentException('Summoner ID must be an integer.');
			}

			$apiUrl = self::$API_URLS['stats'] . '/by-summoner/' . $summonerId . '/ranked';
			$params = array();

			if (is_int($season)) {
				$params['season'] = 'SEASON' . $season;
			}

			$rankedStats = $this->sendApiRequest($apiUrl, $params);

			return $rankedStats['champions'];
		}

		public function getMasteries($summonerId) {
			if (!is_int($summonerId)) {
				throw new \InvalidArgumentException('Summoner ID must be an integer.');
			}

			$apiUrl = self::$API_URLS['summoner'] . '/' . $summonerId . '/masteries';
			$masteries = $this->sendApiRequest($apiUrl);

			return $masteries['pages'];
		}

		public function getRunes($summonerId) {
			if (!is_int($summonerId)) {
				throw new \InvalidArgumentException('Summoner ID must be an integer.');
			}

			$apiUrl = self::$API_URLS['summoner'] . '/' . $summonerId . '/runes';
			$runes = $this->sendApiRequest($apiUrl);

			return $runes['pages'];
		}

		public function getSummonerByName($summonerName) {
			if (!is_string($summonerName)) {
				throw new \InvalidArgumentException('Summoner name must be a string.');
			}

			$apiUrl = self::$API_URLS['summoner'] . '/by-name/' . $summonerName;
			$summoner = $this->sendApiRequest($apiUrl);

			return $summoner;
		}

		public function getSummonerById($summonerId) {
			if (!is_int($summonerId)) {
				throw new \InvalidArgumentException('Summoner ID must be an integer.');
			}

			$apiUrl = self::$API_URLS['summoner'] . '/' . $summonerId;
			$summoner = $this->sendApiRequest($apiUrl);

			return $summoner;
		}

		public function getSummonerNamesByIds($summonerIdsList) {
			if (!is_array($summonerIdsList)) {
				throw new \InvalidArgumentException('Summoner IDs must be an array.');
			}

			$summonerIds = implode($summonerIdsList, ',');

			$apiUrl = self::$API_URLS['summoner'] . '/' . $summonerIds . '/name';
			$summonerNames = $this->sendApiRequest($apiUrl);

			return $summonerNames['summoners'];
		}

		public function getTeams($summonerId) {
			if (!is_int($summonerId)) {
				throw new \InvalidArgumentException('Summoner ID must be an integer.');
			}

			$apiUrl = self::$API_URLS['team'] . '/by-summoner/' . $summonerId;
			$teams = $this->sendApiRequest($apiUrl);

			return $teams;
		}

		private static function buildApiUrl($operation) {
			$version = ApiVersion::${$operation};
			$isStaticData = $operation === 'staticData';

			$url = self::BASE_URL . self::BASE_API_PATH;
			$url = self::bind($url, 'static-data', $isStaticData ? '/static-data' : '');
			$url = self::bind($url, 'version', $version);
			$url = self::bind($url, 'operation', $isStaticData ? '' : '/' . $operation);

			return $url;
		}

		private static function buildObserverUrl($url, $operation) {
			$version = ApiVersion::${$operation};

			$url = self::BASE_URL . self::BASE_OBSERVER_PATH . $url;
			$url = self::bind($url, 'version', $version);

			return $url;
		}

		/**
		 * Bind a value to a {key} in a template string.
		 * @param  string $template The string with the key needed to be bound.
		 * @param  string $key      The {key} in the template to be replaced.
		 * @param  string $value    The value to replace the {key} with.
		 * @return string           The template with the value bound to the
		 *                          key.
		 */
		private static function bind($template, $key, $value) {
			return str_replace('{' . $key . '}', $value, $template);
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
			static $CURL_METHODS = array(
				'GET' => CURLOPT_HTTPGET,
				'POST' => CURLOPT_POST
			);

			$httpQuery = http_build_query($params);
			$url .= '?' . $httpQuery;

			$ch = curl_init();

			if (!isset($CURL_METHODS[$method])) {
				throw new \InvalidArgumentException('Invalid HTTP method; must be either GET or POST.');
			}

			$curlMethod = $CURL_METHODS[$method];

			// Verify SSL certs
			// Download a copy from http://curl.haxx.se/ca/cacert.pem and save
			// to certs/cacert.pem to prevent MITM attacks.
			//
			// More info: http://stackoverflow.com/q/6400300/558592
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '/certs/cacert.pem');

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, $curlMethod, true);

			// Get the response as a string
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$jsonResponse = curl_exec($ch);

			curl_close($ch);

			// Use an associative array rather than an object
			$response = json_decode($jsonResponse, true);

			if (json_last_error() !== JSON_ERROR_NONE) {
				throw new \Exception(json_last_error_msg());
			}

			return $response;
		}

		private function sendApiRequest($url, $params = array()) {
			// Final touches on URL and params
			$url = self::bind($url, 'region', strtolower($this->region));
			$params['api_key'] = $this->apiKey;

			return self::getJsonResponse($url, $params);
		}
	}

	Api::init();
