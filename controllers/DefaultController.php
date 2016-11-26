<?php
/**
 * @Author: Jakub Hrášek
 * @Date:   2016-11-25 12:19:51
 * @Last Modified by:   Jakub Hrášek
 * @Last Modified time: 2016-11-26 15:51:04
 */

namespace api\controllers;


use Yii;

class DefaultController extends ActiveController
{
	/**
	 * @inheritdoc
	 */
	public function init()
	{
		if ($this->modelClass === null) {
			$this->modelClass = $this->module->modelNamespace . '\\' . self::dashesToCamelCase($this->id);
		}

		parent::init();
	}


	/**
	 * Convert dashed string from url to camel case.
	 *
	 * @param string dashed string.
	 * @param boolean capitalize first character
	 * @return string camel cased string
	 */
	public static function dashesToCamelCase($string, $capitalizeFirstCharacter = true)
	{
		$str = str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));

		if (!$capitalizeFirstCharacter) {
			$str[0] = strtolower($str[0]);
		}

		return $str;
	}

}