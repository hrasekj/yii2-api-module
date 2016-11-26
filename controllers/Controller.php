<?php
/**
 * @Author: Jakub Hrášek
 * @Date:   2016-11-24 03:33:47
 * @Last Modified by:   Jakub Hrášek
 * @Last Modified time: 2016-11-26 15:51:07
 */

namespace api\controllers;


use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\ContentNegotiator;
use yii\filters\RateLimiter;
use yii\web\Response;
use yii\filters\VerbFilter;

class Controller extends \yii\rest\Controller
{
	/**
	 * @var string|array the configuration for creating the serializer that formats the response data.
	 */
	public $serializer = [
		'class' => 'yii\rest\Serializer',
		'collectionEnvelope' => 'items',
	];


	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			'contentNegotiator' => [
				'class' => ContentNegotiator::className(),
				'formats' => [
					'application/json' => Response::FORMAT_JSON,
				],
			],
			'verbFilter' => [
				'class' => VerbFilter::className(),
				'actions' => $this->verbs(),
			],
			'authenticator' => [
				'class' => CompositeAuth::className(),
				// 'authMethods' => [HttpBasicAuth::className()],
			],
			'rateLimiter' => [
				'class' => RateLimiter::className(),
			],
		];
	}

}