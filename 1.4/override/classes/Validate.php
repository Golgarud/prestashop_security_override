<?php
class Validate extends ValidateCore
{
	/**
	 * Check for password validity
	 *
	 * @param string $passwd Password to validate
	 * @param int $size
	 * @return boolean Validity is ok or not
	 * override prestashop security
	 * https://gist.github.com/xBorderie/15c48651e5c91ba0141f/6fcffc3bc37523235fa92a1a5276008ed3fed45c
	 */
	public static function isPasswd($passwd, $size = 5)
	{
		return (strlen($passwd) >= $size && strlen($passwd) < 255);
	}
}
