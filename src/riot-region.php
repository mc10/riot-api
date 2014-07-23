<?php
	namespace RiotApi;

	final class Region {
		public static $REGIONS = array('BR', 'EUNE', 'EUW', 'KR', 'LAN', 'LAS', 'NA', 'OCE', 'RU', 'TR');

		private function __construct() {}

		public static function isRegion($region) {
			return in_array($region, self::$REGIONS, true);
		}
	}
